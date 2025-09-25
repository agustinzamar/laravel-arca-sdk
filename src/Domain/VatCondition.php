<?php

namespace AgustinZamar\LaravelArcaSdk\Domain;

class VatCondition
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}

}
