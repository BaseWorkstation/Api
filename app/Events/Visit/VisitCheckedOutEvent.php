<?php

namespace App\Events\Visit;

use Illuminate\Http\Request;
use App\Models\Visit;
use Illuminate\Support\Facades\Log;

class VisitCheckedOutEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Request $request
     * @var  Visit $visit
     */
    public $request;
    public $visit;

    /**
     * Dependency Injection of variables
     *
     * @param Request $request
     * @param Visit $visit
     * @return void
     */
    public function __construct(Request $request, Visit $visit)
    {
        Log::info('event fired!');
        $this->request = $request;
        $this->visit = $visit;
    }
}
