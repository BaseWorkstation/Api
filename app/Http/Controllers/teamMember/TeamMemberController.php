<?php

namespace App\Http\Controllers\teamMember;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\TeamMemberRepository;
use Illuminate\Validation\Rule;
use App\Models\Team;

class TeamMemberController extends Controller
{
    /**
     * declaration of teamMember repository
     *
     * @var teamMemberRepository
     */
    private $teamMemberRepository;

    /**
     * Dependency Injection of teamMemberRepository.
     *
     * @param  \App\Repositories\TeamMemberRepository  $teamMemberRepository
     * @return void
     */
    public function __construct(TeamMemberRepository $teamMemberRepository)
    {
        $this->teamMemberRepository = $teamMemberRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\TeamMemberRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->teamMemberRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\TeamMemberRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        // authorization
        $this->authorize('createMember', Team::findOrFail($request->team_id));

        // validation
        $request->validate([
            'emails' => 'required|array',
        ]);

        // run in the repository
        return $this->teamMemberRepository->store($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\TeamMemberRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function remove(Request $request)
    {
        // authorization
        $this->authorize('removeMember', Team::findOrFail($request->team_id));

        // run in the repository
        return $this->teamMemberRepository->destroy($request);
    }
}
