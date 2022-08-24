<?php
 
namespace App\Listeners\Visit;
 
use App\Repositories\VisitRepository;
use App\Events\Visit\VisitCheckedInEvent;
use App\Events\Visit\VisitCheckedOutEvent;
use App\Listeners\Visit\VisitEventSubscriber;
use App\Notifications\NotifyWorkstationOfNewVisit;
use App\Notifications\NotifyWorkstationOfCheckoutOTP;
use App\Models\Workstation;
 
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
     */
    public function sendOTPNotificationToWorkstation($event) 
    {
        // get workstation
        $workstation = Workstation::findOrFail($event->visit->workstation_id);

        // send notification to workstation
        $workstation->notify(new NotifyWorkstationOfCheckoutOTP($event->visit));
    }

    /**
     * Handle event.
     */
    public function sendCheckedInNotificationToWorkstation($event) 
    {
        // get workstation
        $workstation = Workstation::findOrFail($event->visit->workstation_id);

        // send notification to workstation
        $workstation->notify(new NotifyWorkstationOfNewVisit($event->visit));
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
            VisitCheckedOutEvent::class,
            [VisitEventSubscriber::class, 'sendOTPNotificationToWorkstation']
        );

        $events->listen(
            VisitCheckedInEvent::class,
            [VisitEventSubscriber::class, 'sendCheckedInNotificationToWorkstation']
        );
    }
}