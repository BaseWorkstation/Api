<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Visit;
use Carbon\Carbon;
use Auth;

class StatRepository
{
    /**
     * get general statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Team\TeamCollection
     */
    public function general(Request $request)
    {
        // save request details in variables
        $keywords = $request->keywords;
        $workstation_id = $request->workstation_id;
        $user_id = $request->user_id;
        $request->from_date? 
            $from_date = $request->from_date."T00:00:00.000Z": 
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $request->to_date? 
            $to_date = $request->to_date."T23:59:59.000Z": 
            $to_date = Carbon::now();

        $data = [];

        // fetch Visits from db using filters when they are available in the request
        $visits = DB::table('visits')
                        ->when($from_date, function ($query, $from_date) {
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
                        });

        // fetch users from db using filters when they are available in the request
        $users = DB::table('users')
                        ->when($from_date, function ($query, $from_date) {
                            return $query->whereDate('created_at', '>=', $from_date );
                        })
                        ->when($to_date, function ($query, $to_date) {
                            return $query->whereDate('created_at', '<=', $to_date );
                        });

        // fetch workstations from db using filters when they are available in the request
        $workstations = DB::table('workstations')
                        ->when($from_date, function ($query, $from_date) {
                            return $query->whereDate('created_at', '>=', $from_date );
                        })
                        ->when($to_date, function ($query, $to_date) {
                            return $query->whereDate('created_at', '<=', $to_date );
                        });

        // fetch teams from db using filters when they are available in the request
        $teams = DB::table('teams')
                        ->when($from_date, function ($query, $from_date) {
                            return $query->whereDate('created_at', '>=', $from_date );
                        })
                        ->when($to_date, function ($query, $to_date) {
                            return $query->whereDate('created_at', '<=', $to_date );
                        });

        // add visits details to returned data
        $data['data']['visits']['all'] = $visits->count();
        $data['data']['visits']['checked_out'] = $visits->whereNotNull('check_out_time')->count();
        $data['data']['visits']['paid'] = $visits->where('paid_status', true)->count();

        // only return some data when the request has workstation_id and user_id e.g. if a someone wants to see stats regarding a workstation, there's no need to return other workstation statistics.
        if (!$request->filled('workstation_id') && !$request->filled('user_id')) {
            // add users details to returned data
            $data['data']['users']['registered']['all'] = $users->count();

            // add workstations details to returned data
            $data['data']['workstations']['all'] = $workstations->count();

            // add teams details to returned data
            $data['data']['teams']['all'] = $teams->count();
        }

        // add revenue details to returned data
        $data['data']['revenue']['all'] = DB::table('visits')
                        ->when($from_date, function ($query, $from_date) {
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
                        ->whereNotNull('check_out_time')
                        ->sum('total_value_of_minutes_spent_in_naira');

        return response()->json($data);
    }
}
