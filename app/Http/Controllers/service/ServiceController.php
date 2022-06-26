<?php

namespace App\Http\Controllers\service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ServiceRepository;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * declaration of user repository
     *
     * @var ServiceRepository
     */
    private $serviceRepository;

    /**
     * Dependency Injection of ServiceRepository.
     *
     * @param  \App\Repositories\ServiceRepository  $serviceRepository
     * @return void
     */
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\ServiceRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->serviceRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\ServiceRepository
     */
    public function store(Request $request)
    {
        // validation
        $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|integer',
            'category' => ["required", Rule::in(config('enums.service_category'))],
        ]);

        // run in the repository
        return $this->serviceRepository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\ServiceRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->serviceRepository->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\ServiceRepository
     */
    public function update(Request $request, $id)
    {
        // validation
        $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|integer',
        ]);

        // run in the repository
        return $this->serviceRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\ServiceRepository
     */
    public function destroy($id)
    {
        // run in the repository
        return $this->workstationRepository->destroy($id);
    }
}
