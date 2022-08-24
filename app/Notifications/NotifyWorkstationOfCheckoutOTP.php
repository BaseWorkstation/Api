<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\VonageMessage;
use App\Models\Visit;
use App\Models\Workstation;
use App\Models\User;

class NotifyWorkstationOfCheckoutOTP extends Notification
{
    use Queueable;

    public $visit;

    /**
     * Create a new notification instance.
     *
     * @param  Visit  $visit
     * @return void
     */
    public function __construct(Visit $visit)
    {
        $this->visit = $visit;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $workstation = Workstation::findOrFail($this->visit['workstation_id']);
        $user = User::findOrFail($this->visit['user_id']);

        return (new MailMessage)
                    ->line(ucfirst($user->first_name).' '.ucfirst($user->last_name).' is checking out of '.ucfirst($workstation->name).'. Use OTP code '. $this->visit['otp'].' to approve. ');
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\VonageMessage
     */
    public function toVonage($notifiable)
    {
        $workstation = Workstation::findOrFail($this->visit['workstation_id']);
        $user = User::findOrFail($this->visit['user_id']);

        return (new VonageMessage)
                    ->content(ucfirst($user->first_name).' '.ucfirst($user->last_name).' is checking out of '.ucfirst($workstation->name).'. Use OTP code '. $this->visit['otp'].' to approve. ');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
