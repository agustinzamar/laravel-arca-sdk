<?php

namespace AgustinZamar\LaravelArcaSdk\Domain;

use AgustinZamar\LaravelArcaSdk\Enums\IdentificationType;

class Identification
{
    public function __construct(
        public readonly IdentificationType $type,
        public readonly float              $number,
    )
    {
    }

    public function getLabel(): string
    {
        return $this->type->getLabel();
    }
}