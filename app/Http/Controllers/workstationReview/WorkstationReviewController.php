<?php

namespace App\Http\Controllers\workstationReview;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\WorkstationReviewRepository;
use Illuminate\Validation\Rule;
use App\Models\Workstation;

class WorkstationReviewController extends Controller
{
    /**
     * declaration of workstationReview repository
     *
     * @var workstationReviewRepository
     */
    private $workstationReviewRepository;

    /**
     * Dependency Injection of WorkstationReviewRepository.
     *
     * @param  \App\Repositories\WorkstationReviewRepository  $workstationReviewRepository
     * @return void
     */
    public function __construct(WorkstationReviewRepository $workstationReviewRepository)
    {
        $this->workstationReviewRepository = $workstationReviewRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\WorkstationReviewRepository
     */
    public function index(Request $request, $id)
    {
        // run in the repository
        return $this->workstationReviewRepository->index($request, $id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\WorkstationReviewRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, $id)
    {
        // authorization
        //$this->authorize('createReview', Workstation::findOrFail($request->workstation_id));

        // validation
        $request->validate([
            'rating' => 'required|integer',
            'review' => 'required|string',
        ]);

        // run in the repository
        return $this->workstationReviewRepository->store($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $workstation_id
     * @param  int  $review_id
     * @return \App\Http\Repositories\WorkstationReviewRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function remove($workstation_id, $review_id)
    {
        // run in the repository
        return $this->workstationReviewRepository->destroy($workstation_id, $review_id);
    }
}
