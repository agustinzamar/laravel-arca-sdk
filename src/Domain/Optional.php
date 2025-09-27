<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Domain;

class Optional
{
    public function __construct(
        public string $id,
        public string $value,
    )
    {
    }
}
