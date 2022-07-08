<?php
 
namespace App\Listeners\Workstation;
 
use App\Repositories\RetainerRepository;
use App\Repositories\WorkstationRepository;
use App\Events\Workstation\NewWorkstationCreatedEvent;
use App\Listeners\Workstation\WorkstationEventSubscriber;
 
class WorkstationEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var RetainerRepository $retainerRepository
     * @var WorkstationRepository $workstationRepository
     */
    public $retainerRepository;
    public $workstationRepository;

    /**
     * Dependency Injection of variables
     *
     * @param RetainerRepository $retainerRepository
     * @param WorkstationRepository $workstationRepository
     * @return void
     */
    public function __construct(RetainerRepository $retainerRepository, WorkstationRepository $workstationRepository)
    {
        $this->retainerRepository = $retainerRepository;
        $this->workstationRepository = $workstationRepository;
    }

    /**
     * Handle storing of retainers.
     */
    public function storeRetainer($event) {
        // run in retainer repository
        $this->retainerRepository->storeRetainerWhenWorkstationIsCreated($event->request, $event->workstation);
    }

    /**
     * Handle event.
     */
    public function saveUserOwnedWorkstation($event) 
    {
        $this->workstationRepository->saveUserOwnedWorkstation($event->request, $event->workstation);
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
            NewWorkstationCreatedEvent::class,
            [WorkstationEventSubscriber::class, 'storeRetainer']
        );

        $events->listen(
            NewWorkstationCreatedEvent::class,
            [WorkstationEventSubscriber::class, 'saveUserOwnedWorkstation']
        );
    }
}