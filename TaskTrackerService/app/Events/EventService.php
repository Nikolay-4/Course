<?php

namespace TaskTracker\Events;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Psy\Exception\ThrowUpException;
use TaskTracker\Events\UserCreatedEvent;
use TaskTracker\Events\UserDeletedEvent;
use TaskTracker\Events\UserUpdatedEvent;

class EventService
{

    private \PhpAmqpLib\Channel\AbstractChannel|\PhpAmqpLib\Channel\AMQPChannel $channel;
    private ?string $exchange;
    private AMQPStreamConnection $rabbitConnection;

    public const TOPIC_REGISTERED = 'registered';
    public const TOPIC_USER_CUD = 'userCUD';

    public const EVENT_CLASS_MAP = [
        'userUpdated' => \TaskTracker\Events\UserUpdatedEvent::class,
        'userCreated' => \TaskTracker\Events\UserCreatedEvent::class,
        'userDeleted' => \TaskTracker\Events\UserDeletedEvent::class,
    ];
    public function __construct(string $topic = null)
    {
        $this->exchange = $topic;

        $this->rabbitConnection = new AMQPStreamConnection(
            config('queue.rabbitmq.host'),
            config('queue.rabbitmq.port'),
            config('queue.rabbitmq.user'),
            config('queue.rabbitmq.password'),
            config('queue.rabbitmq.vhost')
        );

        $this->channel = $this->rabbitConnection->channel();
        if ($this->exchange) {
            $this->channel->exchange_declare($this->exchange, AMQPExchangeType::FANOUT, false, true, false);
        }

    }

    public function __destruct()
    {
        $this->channel->close();
        $this->rabbitConnection->close();
    }

    public function listen(string $queue, callable $handler, array $topics = null)
    {
        $topics ??= [$this->exchange];
        $this->channel->queue_declare($queue, false, true, false, false);
        foreach ($topics as $exchange) {
            $this->channel->queue_bind($queue, $exchange);
        }

        $this->channel->basic_consume($queue, callback: function (AMQPMessage $msg) use ($handler) {
            try {
                $data = json_decode($msg->body, true);

                $eventType = $data['eventName'];
                $event = call_user_func([self::EVENT_CLASS_MAP[$eventType], 'fromArray'], $data);
                $handler($event);
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                Log::error('Event aborted');
            }
            $msg->ack();
        });

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

    }

    public function emit(array $eventData)
    {
        $msg = new AMQPMessage(json_encode($eventData));
        $this->channel->basic_publish($msg, $this->exchange);
    }

}
