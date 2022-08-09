<?php
 
namespace App\Listeners\Team;
 
use App\Repositories\TeamRepository;
use App\Repositories\PlanRepository;
use App\Events\Team\NewTeamCreatedEvent;
use App\Events\TeamMember\NewRegisteredTeamMemberInvitedEvent;
use App\Events\TeamMember\NewUnRegisteredTeamMemberInvitedEvent;
use App\Listeners\Team\TeamEventSubscriber;
use App\Notifications\RegisteredTeamMemberInvited;
use App\Notifications\UnRegisteredTeamMemberInvited;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
 
class TeamEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var TeamRepository $teamRepository
     * @var PlanRepository $planRepository
     */
    public $teamRepository;
    public $planRepository;

    /**
     * Dependency Injection of variables
     *
     * @param TeamRepository $teamRepository
     * @return void
     */
    public function __construct(TeamRepository $teamRepository, PlanRepository $planRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->planRepository = $planRepository;
    }

    /**
     * Handle event.
     */
    public function saveUserOwnedTeam($event) 
    {
        $this->teamRepository->saveUserOwnedTeam($event->request, $event->team);
    }

    /**
     * Handle event.
     */
    public function addPlansToTeam($event) 
    {
        $this->planRepository->addPlansToTeam($event->request, $event->team);
    }

    /**
     * Handle event.
     */
    public function sendNotificationAboutNewRegisteredTeamMemberInvite($event) 
    {
        $event->user->notify(new RegisteredTeamMemberInvited($event->user, $event->team));
    }

    /**
     * Handle event.
     */
    public function sendNotificationAboutNewUnRegisteredTeamMemberInvite($event) 
    {
        Log::info('got to the handler');
        Notification::route('mail', $event->email)->notify(new UnRegisteredTeamMemberInvited($event->email, $event->team));
    }
 
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            NewTeamCreatedEvent::class,
            [TeamEventSubscriber::class, 'saveUserOwnedTeam']
        );

        /*$events->listen(
            NewTeamCreatedEvent::class,
            [TeamEventSubscriber::class, 'addPlansToTeam']
        );
        */

        $events->listen(
            NewRegisteredTeamMemberInvitedEvent::class,
            [TeamEventSubscriber::class, 'sendNotificationAboutNewRegisteredTeamMemberInvite']
        );

        $events->listen(
            NewUnRegisteredTeamMemberInvitedEvent::class,
            [TeamEventSubscriber::class, 'sendNotificationAboutNewUnRegisteredTeamMemberInvite']
        );
    }
}