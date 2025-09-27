<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Domain;

class Observation
{
    public function __construct(
        public int    $code,
        public string $message,
    )
    {
    }
}
