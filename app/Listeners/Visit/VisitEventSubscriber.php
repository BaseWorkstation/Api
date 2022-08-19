<?php
 
namespace App\Listeners\Visit;
 
use App\Repositories\VisitRepository;
use App\Events\Visit\VisitCheckedOutEvent;
use App\Listeners\Visit\VisitEventSubscriber;
 
class VisitEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var VisitRepository $visitRepository
     */
    public $visitRepository;

    /**
     * Dependency Injection of variables
     *
     * @param VisitRepository $visitRepository
     * @return void
     */
    public function __construct(VisitRepository $visitRepository)
    {
        $this->visitRepository = $visitRepository;
    }

    /**
     * Handle event.
     
    public function makePaymentForVisit($event) 
    {
        $this->visitRepository->makePaymentForVisit($event->request, $event->visit);
    }
    */
 
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        /*$events->listen(
            VisitCheckedOutEvent::class,
            [VisitEventSubscriber::class, 'makePaymentForVisit']
        );*/
    }
}