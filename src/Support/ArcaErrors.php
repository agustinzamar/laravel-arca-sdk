<?php

namespace AgustinZamar\LaravelArcaSdk\Support;

use Illuminate\Support\Collection;
use stdClass;

final class ArcaErrors
{
    public function __construct(private readonly Collection $errors = new Collection) {}

    public static function fromResponse(stdClass $errors): self
    {
        $instance = new self;

        foreach ($errors as $error) {
            if (is_object($error) && property_exists($error, 'Code') && property_exists($error, 'Msg')) {
                $instance->errors->push(ArcaError::fromResponse($error));
            }
        }

        return $instance;
    }
}
