<?php

namespace AgustinZamar\LaravelArcaSdk\Contracts\Response;

class VatConditionResponse
{

    public function __construct(
        public readonly int    $id,
        public readonly string $name,
    )
    {
    }

}