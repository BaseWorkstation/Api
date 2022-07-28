<?php

namespace App\Repositories;

use App\Http\Resources\Visit\VisitResource;
use App\Http\Resources\Visit\VisitCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Visit;
use App\Models\Team;
use App\Models\User;
use App\Models\Service;
use App\Models\Workstation;
use Carbon\Carbon;

class VisitRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Visit\VisitCollection
     */
    public function index(Request $request)
    {
        // save request details in variables
        $user_id = $request->user_id;
        $workstation_id = $request->workstation_id;
        $request->from_date? 
            $from_date = $request->from_date."T00:00:00.000Z": 
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $request->to_date? 
            $to_date = $request->to_date."T23:59:59.000Z": 
            $to_date = Carbon::now();

        // fetch Visits from db using filters when they are available in the request
        $visits = Visit::when($from_date, function ($query, $from_date) {
                            return $query->whereDate('created_at', '>=', $from_date );
                        })
                        ->when($to_date, function ($query, $to_date) {
                            return $query->whereDate('created_at', '<=', $to_date );
                        })
                        ->when($user_id, function ($query, $user_id) {
                            return $query->where('user_id', $user_id);
                        })
                        ->when($workstation_id, function ($query, $workstation_id) {
                            return $query->where('workstation_id', $workstation_id);
                        })
                        ->latest();

        // if user asks that the result be paginated
        if ($request->filled('paginate') && $request->paginate) {
            return new VisitCollection($visits->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new VisitCollection($visits->get());
    }

    /**
     * check-in a new visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function checkIn(Request $request)
    {
        // fetch user
        $user = User::where('unique_pin', $request->unique_pin)->get()->first();
        // fetch service
        $service = Service::findOrFail($request->service_id);
        // fetch workstation
        $workstation = $service->workstation;

        // if user has previously checked-in but didn't check out, return 401
        $visit = Visit::where('user_id', $user->id)->whereNull('check_out_time')->latest()->get()->first();
        if ($visit) {
            return response(['error' => 'you still have a visit that is not checked out yet'], 401);
        }

        // check if user and workstation exists
        if ($user && $workstation) {
            // persist request details and store in a variable
            $visit = Visit::firstOrCreate([
                "user_id" => $user->id,
                "workstation_id" => $workstation->id,
                "check_in_time" => Carbon::now(),
            ]);

            // add service to visit
            $visit->services()->syncWithoutDetaching($service->id);

            // return resource
            return new VisitResource($visit);
        }

        return response(['error' => 'can not verify user'], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function show($id)
    {
        // return resource
        return new VisitResource(Visit::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $visit = $this->getVisitById($id);

        // remove or filter null values from the request data then update the instance
        $visit->update(array_filter($request->all()));

        // return resource
        return new VisitResource($visit);
    }

    /**
     * find a specific Visit using ID.
     *
     * @param  int  $id
     * @return \App\Models\Visit
     */
    public function getVisitById($id)
    {
        // find and return the instance
        return Visit::findOrFail($id);
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
        $this->getVisitById($id)->delete();
    }
}
