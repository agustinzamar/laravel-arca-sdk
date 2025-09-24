<?php

namespace AgustinZamar\LaravelArcaSdk\Domain;

class InvoiceType
{

    public function __construct(
        public readonly int    $id,
        public readonly string $name,
    )
    {
    }

}