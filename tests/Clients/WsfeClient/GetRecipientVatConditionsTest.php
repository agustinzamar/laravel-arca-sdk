<?php

use AgustinZamar\LaravelArcaSdk\Clients\WsaaClient;
use AgustinZamar\LaravelArcaSdk\Clients\WsfeClient;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\VatConditionResponse;
use AgustinZamar\LaravelArcaSdk\Domain\AuthorizationTicket;
use AgustinZamar\LaravelArcaSdk\Support\ArcaErrors;
use Illuminate\Support\Collection;

beforeEach(function () {
    // Mock WsaaClient
    $this->wsaaClientMock = Mockery::mock(WsaaClient::class);
    $this->wsaaClientMock->shouldReceive('getAuthorizationTicket')->andReturn(new AuthorizationTicket(
        token: 'random-token',
        sign: 'random-sign',
        expiration: now()->addHours(12)
    ));

    $this->wsfeClient = Mockery::mock(WsfeClient::class)->makePartial();
});

afterEach(function () {
    Mockery::close();
});

test('it can get recipient vat conditions', function () {
    // Mock SOAP response structure
    $mockResponse = (object) [
        'FEParamGetCondicionIvaReceptorResult' => (object) [
            'Errors' => null,
            'Events' => null,
            'ResultGet' => (object) [
                'CondicionIvaReceptor' => [
                    (object) ['Id' => 1, 'Desc' => 'IVA Responsable Inscripto'],
                    (object) ['Id' => 6, 'Desc' => 'Responsable Monotributo'],
                    (object) ['Id' => 13, 'Desc' => 'Monotributista Social'],
                    (object) ['Id' => 16, 'Desc' => 'Monotributo Trabajador Independiente Promovido'],
                    (object) ['Id' => 4, 'Desc' => 'IVA Sujeto Exento'],
                    (object) ['Id' => 7, 'Desc' => 'Sujeto No Categorizado'],
                    (object) ['Id' => 8, 'Desc' => 'Proveedor del Exterior'],
                    (object) ['Id' => 9, 'Desc' => 'Cliente del Exterior'],
                    (object) ['Id' => 10, 'Desc' => 'IVA Liberado – Ley N° 19.640'],
                    (object) ['Id' => 15, 'Desc' => 'IVA No Alcanzado'],
                    (object) ['Id' => 5, 'Desc' => 'Consumidor Final'],
                ],
            ],
        ],
    ];

    // Enable protected method mocking and mock the call method
    $this->wsfeClient->shouldAllowMockingProtectedMethods()
        ->shouldReceive('call')
        ->with('FEParamGetCondicionIvaReceptor')
        ->once()
        ->andReturn($mockResponse);

    // Call the real method which will use our mocked call method
    $result = $this->wsfeClient->getRecipientVatConditions();

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(11);
    expect($result)->each->toBeInstanceOf(VatConditionResponse::class);

    // Check first item
    expect($result->first())->toBeInstanceOf(VatConditionResponse::class);
    expect($result->first()->id)->toBe(1);
    expect($result->first()->name)->toBe('IVA Responsable Inscripto');
});

test('it handles error response when getting recipient vat conditions', function () {
    // Mock SOAP error response structure
    $mockErrorResponse = (object) [
        'FEParamGetCondicionIvaReceptorResult' => (object) [
            'Errors' => (object) [
                (object) [
                    'Code' => '600',
                    'Msg' => 'No autorizado.',
                ],
                (object) [
                    'Code' => '601',
                    'Msg' => 'CUIT representada no se encuentra registrada en los padrones de AFIP.',
                ],
            ],
            'Events' => null,
            'ResultGet' => null,
        ],
    ];

    // Enable protected method mocking and mock the call method
    $this->wsfeClient->shouldAllowMockingProtectedMethods()
        ->shouldReceive('call')
        ->with('FEParamGetCondicionIvaReceptor')
        ->once()
        ->andReturn($mockErrorResponse);

    // Call the real method which will use our mocked call method
    $result = $this->wsfeClient->getRecipientVatConditions();

    expect($result)->toBeInstanceOf(ArcaErrors::class);
});
