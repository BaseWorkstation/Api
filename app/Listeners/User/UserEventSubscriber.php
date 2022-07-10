<?php
 
namespace App\Listeners\User;
 
use App\Repositories\UserRepository;
use App\Events\User\NewUserCreatedEvent;
use App\Listeners\User\UserEventSubscriber;
use Illuminate\Support\Facades\Log;
use App\Notifications\UserPinCreated;
 
class UserEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var UserRepository $userRepository
     */
    public $userRepository;

    /**
     * Dependency Injection of variables
     *
     * @param UserRepository $userRepository
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * send pin notification.
     */
    public function sendNotificationAboutUserPin($event) {
        $event->user->notify(new UserPinCreated($event->user));
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
            NewUserCreatedEvent::class,
            [UserEventSubscriber::class, 'sendNotificationAboutUserPin']
        );
    }
}