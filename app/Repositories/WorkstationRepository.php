<?php

namespace App\Repositories;

use App\Http\Resources\Workstation\WorkstationResource;
use App\Http\Resources\Workstation\WorkstationCollection;
use App\Events\Workstation\NewWorkstationCreatedEvent;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Workstation;
use App\Models\Amenity;
use Carbon\Carbon;
use Auth;

class WorkstationRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Workstation\WorkstationCollection
     */
    public function index(Request $request)
    {
        // save request details in variables
        $keywords = $request->keywords;
        $request->from_date? 
            $from_date = $request->from_date."T00:00:00.000Z": 
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $request->to_date? 
            $to_date = $request->to_date."T23:59:59.000Z": 
            $to_date = Carbon::now();

        // fetch workstations from db using filters when they are available in the request
        $workstations = Workstation::when($keywords, function ($query, $keywords) {
                                        return $query->where("name", "like", "%{$keywords}%");
                                    })
                                    ->when($from_date, function ($query, $from_date) {
                                        return $query->whereDate('created_at', '>=', $from_date );
                                    })
                                    ->when($to_date, function ($query, $to_date) {
                                        return $query->whereDate('created_at', '<=', $to_date );
                                    })
                                    ->latest();

        // if user asks that the result be paginated
        if ($request->filled('paginate') && $request->paginate) {
            return new WorkstationCollection($workstations->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new WorkstationCollection($workstations->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Workstation\WorkstationResource
     */
    public function store(Request $request)
    {
        // add +234 to phone number
        $request->phone? $request->request->add(["phone" => '+234' . substr($request->phone, 1)]): null ;

        // persist request details and store in a variable
        $workstation = Workstation::create($request->all());

        // call event that a new workstation has been created
        event(new NewWorkstationCreatedEvent($request, $workstation));

        // return resource
        return new WorkstationResource($workstation);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Workstation\WorkstationResource
     */
    public function show($id)
    {
        // return resource
        return new WorkstationResource(Workstation::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Workstation\WorkstationResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $workstation = $this->getWorkstationById($id);

        // remove or filter null values from the request data then update the instance
        $workstation->update(array_filter($request->all()));

        // update workstation schedule
        $this->storeSchedule($request, $workstation);

        // return resource
        return new WorkstationResource($workstation);
    }

    /**
     * find a specific workstation using ID.
     *
     * @param  int  $id
     * @return \App\Models\Workstation
     */
    public function getWorkstationById($id)
    {
        // find and return the instance
        return Workstation::findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy($id)
    {
        // softdelete instance
        $this->getWorkstationById($id)->delete();
    }

     /**
     * attach newly created workstation to its owner
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workstation  $workstation
     * @return array
     */
    public function saveUserOwnedWorkstation(Request $request, $workstation)
    {
        $check = DB::table('workstation_owners_pivot')
                            ->where([
                                'workstation_id' => $workstation->id,
                                'user_id' => Auth::id(),
                            ])->first();

        if (!$check) {
            $new_entry = DB::table('workstation_owners_pivot')
                                ->insert([
                                    'workstation_id' => $workstation->id,
                                    'user_id' => Auth::id(),
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                ]);

            // give user role of workstation_owner
            Auth::user()->assignRole('workstation_owner');

            return $new_entry;
        }

    }

     /**
     * create a Qr code for the workstation
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workstation  $workstation
     * @return void
     */
    public function createQrCodeForWorkstation(Request $request, $workstation)
    {
        // create meta data for qr code in a variable
        $metadata_for_qr_code = env('APP_URL_FRONT_END').'/check-in/?workstation_id='.$workstation->id.'&workstation_name='.$workstation->name;

        // generate new code
        $qr_code = QrCode::size(500)
                            ->format('svg')
                            //->style('round')
                            //->color(25, 25, 112)
                            //->eyeColor(0, 169, 92, 104, 99, 3, 48)
                            //->eyeColor(1, 128, 0, 32, 191, 64, 191)
                            //->eyeColor(2, 170, 51, 106, 72, 50, 72)
                            //->backgroundColor(229, 228, 226)
                            ->generate($metadata_for_qr_code);

        if (env('APP_INSTALLATION_LOCATION') === 'local') {
            $this->localQrCodeUpload($qr_code, $workstation);
        }

        if (env('APP_INSTALLATION_LOCATION') === 'cloud') {
            $this->cloudQrCodeUpload($qr_code, $workstation);
        }
    }

     /**
     * store amenities that are used by the workstation
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workstation  $workstation
     * @return void
     */
    public function storeAmenities(Request $request, $workstation)
    {
        if ($request->filled('amenities')) {
               foreach ($request->amenities as $amenity_id) {
                   $amenity = Amenity::findOrFail($amenity_id);

                   $workstation->amenities()->syncWithoutDetaching($amenity->id);
               }
           }   
    }

     /**
     * store schedule that are used by the workstation
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workstation  $workstation
     * @return void
     */
    public function storeSchedule(Request $request, $workstation)
    {
        $schedule = $request->schedule;

        if ($request->filled('schedule')) {
               // check if user attempts to save schedule weekdays open_time before persisting
                if (isset($schedule['weekdays']) && isset($schedule['weekdays']['open_time'])) {
                    $workstation->settings()->set('schedule.weekdays.open_time', $schedule['weekdays']['open_time']);
                }
               // check if user attempts to save schedule weekdays close_time before persisting
                if (isset($schedule['weekdays']) && isset($schedule['weekdays']['close_time'])) {
                    $workstation->settings()->set('schedule.weekdays.close_time', $schedule['weekdays']['close_time']);
                }
               // check if user attempts to save schedule weekends open_time before persisting
                if (isset($schedule['weekends']) && isset($schedule['weekends']['open_time'])) {
                    $workstation->settings()->set('schedule.weekends.open_time', $schedule['weekends']['open_time']);
                }
               // check if user attempts to save schedule weekends close_time before persisting
                if (isset($schedule['weekends']) && isset($schedule['weekends']['close_time'])) {
                    $workstation->settings()->set('schedule.weekends.close_time', $schedule['weekends']['close_time']);
                }
           }   
    }

    /**
     * upload qr_code using the local file storage
     *
     * @param  QrCode  $qr_code
     * @param  Workstation  $workstation
     * @return void
     */
    public function localQrCodeUpload($qr_code, Workstation $workstation)
    {
        // save file in storage
        Storage::put('public/qr_codes/'.$workstation->id.'.svg', $qr_code);

        //  update workstation instance to include qr_code_path
        $workstation->qr_code_path = env('APP_URL_SERVER_END').'/storage/qr_codes/'.$workstation->id.'.svg';
        $workstation->save();
    }

    /**
     * upload qr_code using the cloud file storage
     *
     * @param  QrCode  $qr_code
     * @param  Workstation  $workstation
     * @return void
     */
    public function cloudQrCodeUpload($qr_code, Workstation $workstation)
    {
        $fileName = 'qr_code_workstation_'.$workstation->id;
        $fileNameWithExtension = '.svg';
        $file = $qr_code;
        $path = $qr_code;
        $options = array("public_id" => $fileName);
        $tags = array( env('APP_NAME'), 'qr_codes', 'workstation');

        // save file first within the app so as to get a file path
        Storage::put('public/qr_codes/'.$workstation->id.'.svg', $qr_code);

        //$qr_code_path = env('APP_URL_SERVER_END').'/storage/qr_codes/'.$workstation->id.'.svg';
        $qr_code_path = public_path('/storage/qr_codes/'.$workstation->id.'.svg');

        // upload new image on cloud
        $cloudder = Cloudder::upload($qr_code_path, $fileName, $options, $tags);
        $image_result = Cloudder::getResult();

        //  update workstation instance to include qr_code_path
        $workstation->qr_code_path = str_replace('http://', 'https://', $image_result['url']);
        $workstation->save();
    }
}
