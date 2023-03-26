<?php

namespace App\Http\Controllers\workstation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\WorkstationRepository;
use Illuminate\Validation\Rule;
use App\Models\Workstation;

class WorkstationController extends Controller
{
    /**
     * declaration of workstation repository
     *
     * @var workstationRepository
     */
    private $workstationRepository;

    /**
     * Dependency Injection of workstationRepository.
     *
     * @param  \App\Repositories\WorkstationRepository  $workstationRepository
     * @return void
     */
    public function __construct(WorkstationRepository $workstationRepository)
    {
        $this->workstationRepository = $workstationRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\WorkstationRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->workstationRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\WorkstationRepository
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        // authorization
        $this->authorize('create', Workstation::class);

        // validation
        $request->validate([
            'name' => 'required|max:255',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country_iso' => 'required',
            'phone' => 'required|unique:workstations|min:11|max:14',
            'email' => 'required|unique:workstations',
            'currency_code' => ['required', Rule::in(config('enums.currency_code'))],
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i'],
            'about' => 'required',
            'coordinates' => 'required',
            'amenities' => 'sometimes|array',
        ]);

        // run in the repository
        return $this->workstationRepository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\WorkstationRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->workstationRepository->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\WorkstationRepository
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        // authorization
        $this->authorize('update', Workstation::find($id));

        // validation
        $request->validate([
            'name' => 'sometimes|max:255',
            'street' => 'sometimes',
            'city' => 'sometimes',
            'state' => 'sometimes',
            'country_iso' => 'sometimes',
            'currency_code' => ["sometimes", Rule::in(config('enums.currency_code'))],
            'phone' => ['sometimes', 'min:13', 'max:20', Rule::unique('workstations')->ignore($id)],
            'email' => ['sometimes', Rule::unique('workstations')->ignore($id)]
        ]);

        // run in the repository
        return $this->workstationRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\WorkstationRepository
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        // authorization
        $this->authorize('delete', Workstation::find($id));

        // run in the repository
        return $this->workstationRepository->destroy($id);
    }
}
