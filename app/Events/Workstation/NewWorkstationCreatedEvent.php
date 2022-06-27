<?php

namespace App\Events\Workstation;

use Illuminate\Http\Request;
use App\Models\Workstation;

class NewWorkstationCreatedEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Request $request
     * @var  Workstation $workstation
     */
    public $request;
    public $workstation;

    /**
     * Dependency Injection of variables
     *
     * @param Request $request
     * @param Workstation $workstation
     * @return void
     */
    public function __construct(Request $request, Workstation $workstation)
    {
        $this->request = $request;
        $this->workstation = $workstation;
    }
}