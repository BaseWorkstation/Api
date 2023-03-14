<?php

namespace App\Listeners\Visit;

use App\Events\Visit\NewVisitCheckedOut;
use App\Repositories\VisitRepository;
use App\Events\Visit\VisitCheckedInEvent;
use App\Events\Visit\VisitCheckedOutEvent;
use App\Listeners\Visit\VisitEventSubscriber;
use App\Models\User;
use App\Notifications\NotifyWorkstationOfNewVisit;
use App\Notifications\NotifyWorkstationOfCheckoutOTP;
use App\Models\Workstation;
use Illuminate\Support\Facades\Log;

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
        // Log::info("noftify working here!");

        // get workstation
        $workstation = Workstation::findOrFail($event->visit->workstation_id);

        // send notification to workstation
        $workstation->notify(new NotifyWorkstationOfNewVisit($event->visit));
    }


  /**
     * @param mixed $user
     * @param mixed $visit
     */
    public function sendSmsOtp($event)
    {

        // get workstation
        $workstation = Workstation::findOrFail($event->visit->workstation_id);
        // $workstation = Workstation::findOrFail($this->visit['workstation_id']);
        $user = User::findOrFail($event->visit['user_id']);

           $curl = curl_init();
        // Define the message content
        $message = "Hello! " . ucfirst($user->first_name) . " " . ucfirst($user->last_name) .
            " is checking out of " . ucfirst($workstation->name) .
            ". Use confirmation code " . $this->visit['otp'] .
            " to approve. This code expires in 20 minutes.";

        // Define the message data as an associative array
        $data = [
            "api_key" => env('TERMII_API_KEY'),
            "to" => $workstation->phone,
            "from" => "N-Alert",
            "sms" => $message,
            "type" => "plain",
            "channel" => "dnd"
        ];

        $post_data = json_encode($data);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ng.termii.com/api/sms/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;
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
            NewVisitCheckedOut::class,
            [VisitEventSubscriber::class, 'sendSmsOtp']
        );

        $events->listen(
            VisitCheckedInEvent::class,
            [VisitEventSubscriber::class, 'sendCheckedInNotificationToWorkstation']
        );
    }
}
