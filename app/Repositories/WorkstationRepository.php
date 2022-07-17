<?php

namespace App\Repositories;

use App\Http\Resources\Workstation\WorkstationResource;
use App\Http\Resources\Workstation\WorkstationCollection;
use App\Events\Workstation\NewWorkstationCreatedEvent;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Workstation;
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
     * @return array
     */
    public function createQrCodeForWorkstation(Request $request, $workstation)
    {
        // create meta data for qr code in a variable
        $metadata_for_qr_code = env('APP_URL_FRONT_END').'/check-in/?workstation_id='.$workstation->id.'&workstation_name='.$workstation->name;

        // generate new code
        $qr_code = QrCode::size(500)
                            ->format('svg')
                            ->style('round')
                            ->color(99, 3, 48)
                            ->eyeColor(0, 169, 92, 104, 25, 25, 112)
                            ->eyeColor(1, 128, 0, 32, 191, 64, 191)
                            ->eyeColor(2, 170, 51, 106, 72, 50, 72)
                            ->generate($metadata_for_qr_code);

        // save file in storage
        Storage::put('public/qr_codes/'.$workstation->id.'.svg', $qr_code);

        //  update workstation instance to include qr_code_path
        $workstation->qr_code_path = Storage::path('public\qr_codes\\'.$workstation->id.'.svg');
        $workstation->save();
    }
}
