<?php

use AgustinZamar\LaravelArcaSdk\Facades\Wsaa;

it('can test', function () {
    $tokenData = Wsaa::getToken('wsfe');

    dd($tokenData);
});
