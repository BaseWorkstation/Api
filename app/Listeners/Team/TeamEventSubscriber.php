<?php
 
namespace App\Listeners\Team;
 
use App\Repositories\TeamRepository;
use App\Events\Team\NewTeamCreatedEvent;
use App\Listeners\Team\TeamEventSubscriber;
 
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
    }
}