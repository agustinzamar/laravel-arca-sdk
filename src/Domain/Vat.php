<?php

namespace AgustinZamar\LaravelArcaSdk\Domain;

class Vat
{
    public function __construct(
        public readonly int $id,
        public readonly float $baseAmount,
        public readonly float $amount
    ) {}
}
