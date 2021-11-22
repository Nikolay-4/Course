<?php

namespace TaskTracker\Console\Commands;

use PhpAmqpLib\Message\AMQPMessage;
use TaskTracker\Events\EventService;
use Illuminate\Console\Command;

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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
            dump($msg);
        }, [EventService::TOPIC_REGISTERED]);
        return Command::SUCCESS;
    }
}
