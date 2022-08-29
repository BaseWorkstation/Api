<?php

namespace App\Http\Controllers\utility;

use App\Repositories\UtilityRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    /**
     * declaration of utility repository
     *
     * @var utilityRepository
     */
    private $utilityRepository;

    /**
     * Dependency Injection of utilityRepository.
     *
     * @param  \App\Repositories\Interfaces\UtilityRepository  $utilityRepository
     * @return void
     */
    public function __construct(UtilityRepository $utilityRepository)
    {
        $this->utilityRepository = $utilityRepository;
    }

    /**
     * get app enums
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getEnums(Request $request)
    {
        return $this->utilityRepository->getEnums($request);
    }
}