<?php

namespace App\Http\Controllers\stat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\StatRepository;
use Illuminate\Validation\Rule;
use App\Models\Team;

class StatController extends Controller
{
    /**
     * declaration of teamMember repository
     *
     * @var statRepository
     */
    private $statRepository;

    /**
     * Dependency Injection of statRepository.
     *
     * @param  \App\Repositories\StatRepository  $statRepository
     * @return void
     */
    public function __construct(StatRepository $statRepository)
    {
        $this->statRepository = $statRepository;
    }

    /**
     * get general statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\StatRepository
     */
    public function general(Request $request)
    {
        // run in the repository
        return $this->statRepository->general($request);
    }
}
