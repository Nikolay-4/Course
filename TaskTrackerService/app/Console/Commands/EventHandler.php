<?php

namespace TaskTracker\Console\Commands;

use Log;
use PhpAmqpLib\Message\AMQPMessage;
use TaskTracker\Events\EventService;
use Illuminate\Console\Command;
use TaskTracker\Events\UserCreatedEvent;
use TaskTracker\Events\UserUpdatedEvent;
use TaskTracker\Models\User;

class EventHandler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:handle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private array $eventHandlers = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->eventHandlers = [
            \TaskTracker\Events\UserUpdatedEvent::class => 'onUpdateUser',
            \TaskTracker\Events\UserCreatedEvent::class => 'onCreateUser',
            \TaskTracker\Events\UserDeletedEvent::class => 'onCreateUser',
        ];
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $eventService = new EventService();
        $eventService->listen('TaskTrackerHandler', function ($msg) {
//            switch ()
            $this->{$this->eventHandlers[get_class($msg)]}($msg);
            dump($msg);
        }, [EventService::TOPIC_REGISTERED, EventService::TOPIC_USER_CUD]);
        return Command::SUCCESS;
    }

    public function onCreateUser(UserCreatedEvent $event)
    {
        $userDto = $event->data;
        User::firstOrCreate(['public_id' => $userDto->publicId], [
            'public_id' => $userDto->publicId,
            'email' => $userDto->email,
            'name' => $userDto->name,
        ]);
        Log::info('User created', [$userDto]);
    }

    public function onUpdateUser(UserUpdatedEvent $event)
    {
        $userDto = $event->data;
        $user = User::updateOrCreate(['public_id' => $userDto->publicId], [
            'public_id' => $userDto->publicId,
            'email' => $userDto->email,
            'name' => $userDto->name,
        ]);
        if ($user->wasRecentlyCreated) {
            Log::info('User created by update event', [$userDto]);
        } else {
            Log::info('User updated', [$userDto]);
        }
    }

}
