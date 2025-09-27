<?php

namespace AgustinZamar\LaravelArcaSdk\Contracts\Response;

class OptionalTypesResponse
{

    public function __construct(
        public readonly string $id,
        public readonly string $description,
    )
    {
    }

}