<?php

namespace App\Events\TeamMember;

use Illuminate\Http\Request;
use App\Models\TeamMember;
use App\Models\Team;

class NewUnRegisteredTeamMemberInvitedEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Team $team
     * @var  string $email
     */
    public $team;
    public $email;

    /**
     * Dependency Injection of variables
     *
     * @param Team $team
     * @param User $email
     * @return void
     */
    public function __construct(Team $team, $email)
    {
        $this->team = $team;
        $this->email = $email;
    }
}