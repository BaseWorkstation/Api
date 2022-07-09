<?php

namespace App\Repositories;

use App\Http\Resources\TeamMember\TeamMemberResource;
use App\Http\Resources\TeamMember\TeamMemberCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use Carbon\Carbon;
use Auth;

class TeamMemberRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Team\TeamCollection
     */
    public function index(Request $request)
    {
        // fetch team
        $team = Team::findOrFail($request->team_id);

        // fetch members
        $members = $team->members();

        // if user asks that the result be paginated
        if ($request->filled('paginate') && $request->paginate) {
            return new TeamMemberCollection($members->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new TeamMemberCollection($members->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Team\TeamResource
     */
    public function store(Request $request)
    {
        // fetch team
        $team = Team::findOrFail($request->team_id);

        foreach ($request->emails as $email) {
            // fetch user via email
            $user = User::where('email', $email)->get()->first();

            // if user has an account, add user as a member of the team on the pivot table then send a notification for the user to verify. Note that user is only verified as a member when email is confirmed.
            if ($user) {
                $this->saveUserAsTeamMember($user, $team);
            } else {
                // if the user has no account, add user's email to pending_team_invites table then send a notification invite to signup on Base
                $this->saveUserAsUnregisteredMember($email['email_id'], $team);
            }
        }

        // return all team members
        return new TeamMemberCollection($team->members);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function destroy(Request $request)
    {
        // fetch pivot entry
        $entry = DB::table('team_members_pivot')
                            ->where([
                                'team_id' => $request->team_id,
                                'user_id' => $request->user_id,
                            ])
                            ->update([
                                'deleted_at' => Carbon::now(),
                            ]);
    }

     /**
     * attach user as member to a team
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return array
     */
    public function saveUserAsTeamMember(User $user, Team $team)
    {
        $check = DB::table('team_members_pivot')
                            ->where([
                                'team_id' => $team->id,
                                'user_id' => $user->id,
                            ])->first();

        if (!$check) {
            $new_entry = DB::table('team_members_pivot')
                                ->insert([
                                    'team_id' => $team->id,
                                    'user_id' => $user->id,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                ]);

            return $new_entry;
        }

    }

     /**
     * save unregistered members invites
     *
     * @param  string $email
     * @param  \App\Models\Team  $team
     * @return array
     */
    public function saveUserAsUnregisteredMember($email, Team $team)
    {
        $check = DB::table('unregistered_members_invites')
                            ->where([
                                'team_id' => $team->id,
                                'email' => $email,
                            ])->first();

        if (!$check) {
            $new_entry = DB::table('unregistered_members_invites')
                                ->insert([
                                    'team_id' => $team->id,
                                    'email' => $email,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                ]);

            return $new_entry;
        }
    }
}
