<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Team;

class RegisteredTeamMemberInvited extends Notification
{
    use Queueable;

    public $user;
    public $team;

    /**
     * Create a new notification instance.
     *
     * @param  User  $user
     * @param  Team  $team
     * @return void
     */
    public function __construct(User $user, Team $team)
    {
        $this->user = $user;
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
                    ->subject('Invitation to join a new team on Base')
                    ->greeting('Hi '. ucfirst($this->user->first_name))
                    ->line('You have been invited by '.$this->team->name.' to become a part of their team')
                    ->action('Accept', url(env('APP_URL_FRONT_END').'/'))
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
