<?php

namespace App\Http\Controllers\team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\TeamRepository;
use Illuminate\Validation\Rule;
use App\Models\Team;

class TeamController extends Controller
{
    /**
     * declaration of team repository
     *
     * @var teamRepository
     */
    private $teamRepository;

    /**
     * Dependency Injection of teamRepository.
     *
     * @param  \App\Repositories\TeamRepository  $teamRepository
     * @return void
     */
    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\TeamRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->teamRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\TeamRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        // authorization
        $this->authorize('create', Team::class);

        // validation
        $request->validate([
            'name' => 'required|max:255',
            'street' => 'sometimes',
            'city' => 'sometimes',
            'state' => 'sometimes',
            'country_iso' => 'sometimes',
            'phone' => 'sometimes|unique:teams',
            'email' => 'sometimes|unique:teams',
        ]);

        // run in the repository
        return $this->teamRepository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\TeamRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->teamRepository->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\TeamRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        // authorization
        $this->authorize('update', Team::findOrFail($id));

        // validation
        $request->validate([
            'name' => 'sometimes|max:255',
            'street' => 'sometimes',
            'city' => 'sometimes',
            'state' => 'sometimes',
            'country_iso' => 'sometimes',
        ]);

        // run in the repository
        return $this->teamRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\TeamRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        // authorization
        $this->authorize('delete', Team::find($id));

        // run in the repository
        return $this->teamRepository->destroy($id);
    }
}
