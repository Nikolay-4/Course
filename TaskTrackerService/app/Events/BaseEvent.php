<?php

namespace TaskTracker\Events;

use Exception;
use Illuminate\Support\Facades\Log;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

abstract class BaseEvent extends DataTransferObject
{

    #[MapFrom('eventId')]
    public string $eventId;
    #[MapFrom('eventName')]
    public string $eventName;
    #[MapFrom('eventVersion')]
    public string $eventVersion;
    #[MapFrom('eventTime')]
    public string $eventTime;
    #[MapFrom('producer')]
    public string $producer;

    protected string $jsonSchemaPath;

    public static function validate($data, $jsonSchemaPath)
    {
//        todo настроить работу с storage, чтобы схема не загружалась каждый раз из файла
        // Validate
        $validator = new Validator;
        $schema = json_decode(file_get_contents($jsonSchemaPath));
        $validator->validate($data, $schema, Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE_TYPES);

        if ($validator->isValid()) {
            return true;
        } else {
//            todo переделать обработку ошибок
            $errMsg = '';
            foreach ($validator->getErrors() as $error) {
                dump($error);
                $errMsg .= "Invalid event: [{$error['property']}]: {$error['message']} \n";
            }
            throw new Exception($errMsg);
        }
    }

}
