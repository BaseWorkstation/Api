<?php

namespace App\Http\Controllers\plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\PlanRepository;
use Illuminate\Validation\Rule;
use App\Models\Plan;

class PlanController extends Controller
{
    /**
     * declaration of Plan repository
     *
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * Dependency Injection of PlanRepository.
     *
     * @param  \App\Repositories\PlanRepository  $planRepository
     * @return void
     */
    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\PlanRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->planRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\PlanRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        // authorization
        $this->authorize('create', Plan::class);

        // validation
        $request->validate([
            'name' => 'required|max:255',
            'price_per_month' => 'required|integer',
            'currency_code' => ["required", Rule::in(config('enums.currency_code'))],
        ]);

        // run in the repository
        return $this->planRepository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\PlanRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->planRepository->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\PlanRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        // authorization
        $this->authorize('update', Plan::findOrFail($id));

        // validation
        $request->validate([
            'name' => 'required|max:255',
            'price_per_month' => 'required|integer',
            'currency_code' => ["required", Rule::in(config('enums.currency_code'))],
        ]);

        // run in the repository
        return $this->planRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\PlanRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        // authorization
        $this->authorize('delete', Plan::findOrFail($id));

        // run in the repository
        return $this->planRepository->destroy($id);
    }
}
