<?php

namespace Auth\Events;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class UserUpdatedEvent extends BaseEvent
{


//    #[MapFrom('data')]
    public UserDto $data;
    public const JSON_SCHEMA_PATH = 'JsonSchemes/updateUserSchema.json';

    public function __construct(...$args)
    {

        // иначе аргументы оборачиваются еще в один массив и падает ошибка
        if (is_array($args[0] ?? null)) {
            $args = $args[0];
        }
        parent::__construct($args);
    }


//    todo возможно лучше передавать объект user
    public static function fromUserData(array $userData, string $producer): UserUpdatedEvent
    {
        $eventData = [
            'eventId' => uniqid(),
            'eventName' => 'userUpdated',
            'eventVersion' => 1.0,
            'eventTime' => time(),
            'producer' => $producer,
            'data' => $userData
        ];

        BaseEvent::validate($eventData, base_path(self::JSON_SCHEMA_PATH));
        return new static($eventData);
    }
}
