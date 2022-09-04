<?php
 
namespace App\Listeners\Service;
 
use App\Repositories\PriceRepository;
use App\Events\Service\NewServiceCreatedEvent;
use App\Events\Service\ServiceUpdatedEvent;
use App\Listeners\Service\ServiceEventSubscriber;
 
class ServiceEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var PriceRepository $priceRepository
     */
    public $priceRepository;

    /**
     * Dependency Injection of variables
     *
     * @param PriceRepository $priceRepository
     * @return void
     */
    public function __construct(PriceRepository $priceRepository)
    {
        $this->priceRepository = $priceRepository;
    }

    /**
     * Handle storing of prices.
     */
    public function storePrice($event) {
        // run in price repository
        $this->priceRepository->store($event->request, $event->service);
    }

    /**
     * Handle updating of prices.
     */
    public function updatePrice($event) {
        // run in price repository
        $this->priceRepository->update($event->service->prices()->first()->id, $event->request->price);
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
            NewServiceCreatedEvent::class,
            [ServiceEventSubscriber::class, 'storePrice']
        );
        $events->listen(
            ServiceUpdatedEvent::class,
            [ServiceEventSubscriber::class, 'updatePrice']
        );
    }
}