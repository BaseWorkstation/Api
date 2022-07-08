<?php

namespace App\Events\Team;

use Illuminate\Http\Request;
use App\Models\Team;

class NewTeamCreatedEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Request $request
     * @var  Team $team
     */
    public $request;
    public $team;

    /**
     * Dependency Injection of variables
     *
     * @param Request $request
     * @param Team $team
     * @return void
     */
    public function __construct(Request $request, Team $team)
    {
        $this->request = $request;
        $this->team = $team;
    }
}