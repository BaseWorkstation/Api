<?php
 
namespace App\Listeners\User;
 
use App\Repositories\UserRepository;
use App\Events\User\NewUserCreatedEvent;
use App\Listeners\User\UserEventSubscriber;
use Illuminate\Support\Facades\Log;
 
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
     * Handle storing of Users.
     */
    public function logEvent($event) {
        Log::info($event->user);
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
            [UserEventSubscriber::class, 'logEvent']
        );
    }
}