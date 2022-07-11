<?php
 
namespace App\Listeners\Team;
 
use App\Repositories\TeamRepository;
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
     */
    public $teamRepository;

    /**
     * Dependency Injection of variables
     *
     * @param TeamRepository $teamRepository
     * @return void
     */
    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
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