<?php

namespace TaskTracker\Events;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class UserCreatedEvent extends BaseEvent
{


//    #[MapFrom('data')]
    public UserDto $data;
    public const JSON_SCHEMA_PATH = 'JsonSchemes/createUserSchema.json';

    private function __construct(...$args)
    {

        // иначе аргументы оборачиваются еще в один массив и падает ошибка
        if (is_array($args[0] ?? null)) {
            $args = $args[0];
        }
        parent::__construct($args);
    }

    public static function fromArray(array $data): UserCreatedEvent
    {
        BaseEvent::validate($data, self::JSON_SCHEMA_PATH);
        return new static($data);
    }
}
