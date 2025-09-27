<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Domain;

class Buyer
{
    public function __construct(
        public Identification $identification,
        public float $percentage,
    ) {}
}
