<?php
 
namespace App\Listeners\Workstation;
 
use App\Repositories\RetainerRepository;
use App\Repositories\WorkstationRepository;
use App\Repositories\ServiceRepository;
use App\Events\Workstation\NewWorkstationCreatedEvent;
use App\Listeners\Workstation\WorkstationEventSubscriber;
 
class WorkstationEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var RetainerRepository $retainerRepository
     * @var WorkstationRepository $workstationRepository
     * @var ServiceRepository $serviceRepository
     */
    public $retainerRepository;
    public $workstationRepository;
    public $serviceRepository;

    /**
     * Dependency Injection of variables
     *
     * @param RetainerRepository $retainerRepository
     * @param WorkstationRepository $workstationRepository
     * @param ServiceRepository $serviceRepository
     * @return void
     */
    public function __construct(RetainerRepository $retainerRepository, WorkstationRepository $workstationRepository, ServiceRepository $serviceRepository)
    {
        $this->retainerRepository = $retainerRepository;
        $this->workstationRepository = $workstationRepository;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Handle storing of retainers.
     */
    public function storeDefaultRetainer($event) {
        // run in retainer repository
        $this->retainerRepository->storeRetainerWhenWorkstationIsCreated($event->request, $event->workstation);
    }

    /**
     * Handle storing of Services.
     */
    public function storeDefaultService($event) {
        // run in service repository
        $this->serviceRepository->storeServiceWhenWorkstationIsCreated($event->request, $event->workstation);
    }

    /**
     * Handle event.
     */
    public function saveUserOwnedWorkstation($event) 
    {
        $this->workstationRepository->saveUserOwnedWorkstation($event->request, $event->workstation);
    }

    /**
     * Handle event.
     */
    public function createQrCodeForWorkstation($event) 
    {
        $this->workstationRepository->createQrCodeForWorkstation($event->request, $event->workstation);
    }

    /**
     * Handle event.
     */
    public function storeAmenities($event) 
    {
        $this->workstationRepository->storeAmenities($event->request, $event->workstation);
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
            [WorkstationEventSubscriber::class, 'storeDefaultRetainer']
        );

        $events->listen(
            NewWorkstationCreatedEvent::class,
            [WorkstationEventSubscriber::class, 'storeDefaultService']
        );

        $events->listen(
            NewWorkstationCreatedEvent::class,
            [WorkstationEventSubscriber::class, 'saveUserOwnedWorkstation']
        );

        $events->listen(
            NewWorkstationCreatedEvent::class,
            [WorkstationEventSubscriber::class, 'createQrCodeForWorkstation']
        );

        $events->listen(
            NewWorkstationCreatedEvent::class,
            [WorkstationEventSubscriber::class, 'storeAmenities']
        );
    }
}