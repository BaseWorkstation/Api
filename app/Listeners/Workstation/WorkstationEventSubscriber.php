<?php
 
namespace App\Listeners\Workstation;
 
use App\Repositories\RetainerRepository;
use App\Events\Workstation\NewWorkstationCreatedEvent;
use App\Listeners\Workstation\WorkstationEventSubscriber;
 
class WorkstationEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var RetainerRepository $retainerRepository
     */
    public $retainerRepository;

    /**
     * Dependency Injection of variables
     *
     * @param RetainerRepository $retainerRepository
     * @return void
     */
    public function __construct(RetainerRepository $retainerRepository)
    {
        $this->retainerRepository = $retainerRepository;
    }

    /**
     * Handle storing of retainers.
     */
    public function storeRetainer($event) {
        // run in retainer repository
        $this->retainerRepository->storeRetainerWhenWorkstationIsCreated($event->request, $event->workstation);
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
    }
}