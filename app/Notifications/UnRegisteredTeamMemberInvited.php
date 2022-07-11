<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Team;

class UnRegisteredTeamMemberInvited extends Notification implements ShouldQueue
{
    use Queueable;

    public $email;
    public $team;

    /**
     * Create a new notification instance.
     *
     * @param  string  $email
     * @param  Team  $team
     * @return void
     */
    public function __construct($email, Team $team)
    {
        $this->email = $email;
        $this->team = $team;
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
        return (new MailMessage)
                    ->subject('Invitation to join '.$this->team->name.' on Base')
                    ->greeting('Hi there ')
                    ->line('You have been invited by '.$this->team->name.' to become a part of their team.')
                    ->line('But we noticed you do not have an acccount yet. Click the button below to begin. Be sure to use the same email that this invite was sent to.')
                    ->action('Register', url(env('APP_URL').'/register'))
                    ->line('you do not need to take a further action if the invite was not meant for you.');
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
