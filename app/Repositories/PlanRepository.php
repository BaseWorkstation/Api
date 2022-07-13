<?php

namespace App\Repositories;

use App\Http\Resources\Plan\PlanResource;
use App\Http\Resources\Plan\PlanCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;
use App\Models\Team;
use Carbon\Carbon;

class PlanRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Plan\PlanCollection
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

        // fetch Plans from db using filters when they are available in the request
        $plans = Plan::when($keywords, function ($query, $keywords) {
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
            return new PlanCollection($plans->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new PlanCollection($plans->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Plan\PlanResource
     */
    public function store(Request $request)
    {
        // persist request details and store in a variable
        $plan = Plan::firstOrCreate($request->all());

        // return resource
        return new PlanResource($plan);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Plan\PlanResource
     */
    public function show($id)
    {
        // return resource
        return new PlanResource(Plan::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Plan\PlanResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $plan = $this->getPlanById($id);

        // remove or filter null values from the request data then update the instance
        $plan->update(array_filter($request->all()));

        // return resource
        return new PlanResource($plan);
    }

    /**
     * find a specific Plan using ID.
     *
     * @param  int  $id
     * @return \App\Models\Plan
     */
    public function getPlanById($id)
    {
        // find and return the instance
        return Plan::findOrFail($id);
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
        $this->getPlanById($id)->delete();
    }

    /**
     * add plans to team.
     *
     * @param  Request  $request
     * @param  Team  $team
     * @return void
     */
    public function addPlansToTeam(Request $request, Team $team)
    {
        // fetch all plans
        $plans = Plan::all();

        // check whether team already has plan attached, if not, add all plans to team
        foreach ($plans as $plan) 
        {
            $check = DB::table('team_plan_pivot')
                                ->where([
                                    'team_id' => $team->id,
                                    'plan_id' => $plan->id,
                                ])->first();

            if (!$check) {
                $new_entry = DB::table('team_plan_pivot')
                                    ->insert([
                                        'team_id' => $team->id,
                                        'plan_id' => $plan->id,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ]);
            }
        }
    }
}
