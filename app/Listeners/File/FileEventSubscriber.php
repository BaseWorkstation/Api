<?php
 
namespace App\Listeners\File;
 
use App\Repositories\FileRepository;
use App\Events\File\NewFileUploadedEvent;
use App\Listeners\File\FileEventSubscriber;
 
class FileEventSubscriber
{
    /**
     * Public declaration of variables.
     *
     * @var FileRepository $fileRepository
     */
    public $fileRepository;

    /**
     * Dependency Injection of variables
     *
     * @param FileRepository $fileRepository
     * @return void
     */
    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * Handle event.
     */
    public function handlePolymorphicRelationship($event) 
    {
        $this->fileRepository->handlePolymorphicRelationship($event->request, $event->file);
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
            NewFileUploadedEvent::class,
            [FileEventSubscriber::class, 'handlePolymorphicRelationship']
        );
    }
}