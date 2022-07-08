<?php

namespace App\Repositories;

use App\Http\Resources\Team\TeamResource;
use App\Http\Resources\Team\TeamCollection;
use App\Events\Team\NewTeamCreatedEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Team;
use Carbon\Carbon;
use Auth;

class TeamRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Team\TeamCollection
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

        // fetch Teams from db using filters when they are available in the request
        $teams = Team::when($keywords, function ($query, $keywords) {
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
            return new TeamCollection($teams->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new TeamCollection($teams->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Team\TeamResource
     */
    public function store(Request $request)
    {
        // persist request details and store in a variable
        $team = Team::create($request->all());

        // call event that a new team has been created
        event(new NewTeamCreatedEvent($request, $team));

        // return resource
        return new TeamResource($team);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Team\TeamResource
     */
    public function show($id)
    {
        // return resource
        return new TeamResource(Team::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Team\TeamResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $team = $this->getTeamById($id);

        // remove or filter null values from the request data then update the instance
        $team->update(array_filter($request->all()));

        // return resource
        return new TeamResource($team);
    }

    /**
     * find a specific Team using ID.
     *
     * @param  int  $id
     * @return \App\Models\Team
     */
    public function getTeamById($id)
    {
        // find and return the instance
        return Team::findOrFail($id);
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
        $this->getTeamById($id)->delete();
    }

     /**
     * attach newly created Team to its owner
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return array
     */
    public function saveUserOwnedTeam(Request $request, $team)
    {
        $check = DB::table('team_owners_pivot')
                            ->where([
                                'team_id' => $team->id,
                                'user_id' => Auth::id(),
                            ])->first();

        if (!$check) {
            $new_entry = DB::table('team_owners_pivot')
                                ->insert([
                                    'team_id' => $team->id,
                                    'user_id' => Auth::id(),
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                ]);

            return $new_entry;
        }

    }
}
