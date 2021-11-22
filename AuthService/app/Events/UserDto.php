<?php

namespace Auth\Events;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class UserDto extends DataTransferObject
{
    #[MapFrom('publicId')]
    public string $publicId;
    #[MapFrom('email')]
    public string $email;
    #[MapFrom('name')]
    public string $name;
}
