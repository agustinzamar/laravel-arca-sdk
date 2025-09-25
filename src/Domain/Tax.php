<?php

namespace AgustinZamar\LaravelArcaSdk\Domain;

class Tax
{
    public function __construct(
        public readonly int $id,
        public readonly string $description,
        public readonly float $baseAmount,
        public readonly float $rate,
        public readonly float $amount
    ) {}
}
