<?php

namespace AgustinZamar\LaravelArcaSdk\Enums;

enum InvoiceCreatedResult: string
{
    case APPROVED = 'A';
    case REJECTED = 'R';
}
