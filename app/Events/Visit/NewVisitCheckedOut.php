<?php

namespace App\Events\Visit;

use App\Models\Visit;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class NewVisitCheckedOut
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
        $this->request = $request;
        $this->visit = $visit;
    }
}
