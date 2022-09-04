<?php

namespace App\Events\Service;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceUpdatedEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Request $request
     * @var  Service $service
     */
    public $request;
    public $service;

    /**
     * Dependency Injection of variables
     *
     * @param Request $request
     * @param Service $service
     * @return void
     */
    public function __construct(Request $request, Service $service)
    {
        $this->request = $request;
        $this->service = $service;
    }
}