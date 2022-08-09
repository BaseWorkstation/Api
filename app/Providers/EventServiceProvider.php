<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\Service\ServiceEventSubscriber;
use App\Listeners\User\UserEventSubscriber;
use App\Listeners\Workstation\WorkstationEventSubscriber;
use App\Listeners\Team\TeamEventSubscriber;
use App\Listeners\File\FileEventSubscriber;
use App\Listeners\Visit\VisitEventSubscriber;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        UserEventSubscriber::class,
        ServiceEventSubscriber::class,
        WorkstationEventSubscriber::class,
        TeamEventSubscriber::class,
        FileEventSubscriber::class,
        VisitEventSubscriber::class,
    ];
}
