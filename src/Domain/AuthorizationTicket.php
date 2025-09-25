<?php

namespace AgustinZamar\LaravelArcaSdk\Domain;

class AuthorizationTicket
{
    public function __construct(
        public readonly string $token,
        public readonly string $sign,
        public readonly string $expiration,
    ) {}

}
