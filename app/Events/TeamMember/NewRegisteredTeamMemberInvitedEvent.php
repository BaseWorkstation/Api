<?php

namespace App\Events\TeamMember;

use Illuminate\Http\Request;
use App\Models\TeamMember;
use App\Models\Team;
use App\Models\User;

class NewRegisteredTeamMemberInvitedEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Team $team
     * @var  User $user
     */
    public $team;
    public $user;

    /**
     * Dependency Injection of variables
     *
     * @param Team $team
     * @param User $user
     * @return void
     */
    public function __construct(Team $team, User $user)
    {
        $this->team = $team;
        $this->user = $user;
    }
}