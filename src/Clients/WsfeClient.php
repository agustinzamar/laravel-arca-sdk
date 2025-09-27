<?php

namespace AgustinZamar\LaravelArcaSdk\Clients;

use AgustinZamar\LaravelArcaSdk\Contracts\Request\CreateInvoiceRequest;
use AgustinZamar\LaravelArcaSdk\Contracts\Request\InvoiceParams;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceCreatedResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceDetailResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\OptionalTypesResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\VatConditionResponse;
use AgustinZamar\LaravelArcaSdk\Domain\Buyer;
use AgustinZamar\LaravelArcaSdk\Domain\Identification;
use AgustinZamar\LaravelArcaSdk\Domain\Observation;
use AgustinZamar\LaravelArcaSdk\Domain\Optional;
use AgustinZamar\LaravelArcaSdk\Domain\RelatedInvoice;
use AgustinZamar\LaravelArcaSdk\Domain\Tax;
use AgustinZamar\LaravelArcaSdk\Domain\Vat;
use AgustinZamar\LaravelArcaSdk\Enums\Currency;
use AgustinZamar\LaravelArcaSdk\Enums\IdentificationType;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceConcept;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceCreatedResult;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use AgustinZamar\LaravelArcaSdk\Enums\RecipientVatCondition;
use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use SoapClient;
use stdClass;
use const SOAP_1_2;

class WsfeClient
{
    protected WsaaClient $wsaaClient;
    protected SoapClient $client;

    public function __construct(WsaaClient $wsaaClient, array $options = [])
    {
        $this->wsaaClient = $wsaaClient;

        $this->client = new SoapClient(config('laravel-arca-sdk.wsfe_wsdl_url'), array_merge([
            'soap_version' => SOAP_1_2,
            'trace' => 1,
            'exceptions' => true,
        ], $options));
    }

    /**
     * Obtain all the recipient VAT conditions
     *
     * @return Collection<VatConditionResponse>
     */
    public function getRecipientVatConditions(): Collection
    {
        $response = $this->client->FEParamGetCondicionIvaReceptor([
            'Auth' => $this->getAuthParams(),
        ]);

        if (isset($response->FEParamGetCondicionIvaReceptorResult->Errors) && !empty($response->FEParamGetCondicionIvaReceptorResult->Errors)) {
            throw new Exception('Error fetching identification types: ' . json_encode($response->FEParamGetCondicionIvaReceptorResult->Errors));
        }

        return collect($response->FEParamGetCondicionIvaReceptorResult->ResultGet->CondicionIvaReceptor)
            ->map(fn($vatCondition) => new VatConditionResponse(
                id: $vatCondition->Id,
                name: $vatCondition->Desc,
            ));
    }

    /**
     * Collection of all the points of sale which are enabled for Web Services usage
     *
     * @return stdClass
     * @throws Exception
     */
    public function getPointsOfSale(): stdClass
    {
        $response = $this->client->FEParamGetPtosVenta([
            'Auth' => $this->getAuthParams(),
        ]);

        if ($response->FEParamGetPtosVentaResult->Errors && !empty($response->FEParamGetPtosVentaResult->Errors)) {
            throw new Exception('Error fetching points of sale: ' . json_encode($response->FEParamGetPtosVentaResult->Errors));
        }

        return $response->FEParamGetPtosVentaResult->ResultGet;
    }

    public function getLastInvoiceNumber(int $pointOfSale, InvoiceType|int $invoiceType): int
    {
        $invoiceType = $invoiceType instanceof InvoiceType ? $invoiceType->value : $invoiceType;

        $response = $this->client->FECompUltimoAutorizado([
            'Auth' => $this->getAuthParams(),
            'PtoVta' => $pointOfSale,
            'CbteTipo' => $invoiceType,
        ]);

        if (isset($response->FECompUltimoAutorizadoResult->Errors) && !empty($response->FECompUltimoAutorizadoResult->Errors)) {
            throw new Exception('Error fetching last invoice number: ' . json_encode($response->FECompUltimoAutorizadoResult->Errors));
        }

        return (int)$response->FECompUltimoAutorizadoResult->CbteNro;
    }

    /**
     * Create an invoice with the given parameters
     *
     * @param InvoiceParams $request
     * @return InvoiceCreatedResponse
     * @throws Exception
     */
    public function generateInvoice(CreateInvoiceRequest $request): InvoiceCreatedResponse
    {
        $request = array_merge(['Auth' => $this->getAuthParams()], $request->toArray());
        $response = $this->client->FECAESolicitar($request);

        if (isset($response->FECAESolicitarResult->Errors) && !empty($response->FECAESolicitarResult->Errors)) {
            throw new Exception('Error creating invoice: ' . json_encode($response->FECAESolicitarResult->Errors));
        }

        $invoiceData = $response->FECAESolicitarResult->FeDetResp->FECAEDetResponse;

        if ($invoiceData->Resultado !== InvoiceCreatedResult::APPROVED->value) {
            throw new Exception('Invoice not approved: ' . json_encode($invoiceData->Observaciones));
        }

        return new InvoiceCreatedResponse(
            concept: InvoiceConcept::from($invoiceData->Concepto),
            identification: new Identification(
                type: IdentificationType::from($invoiceData->DocTipo),
                number: $invoiceData->DocNro,
            ),
            invoiceFrom: $invoiceData->CbteDesde,
            invoiceTo: $invoiceData->CbteHasta,
            invoiceDate: Carbon::createFromFormat('Ymd', $invoiceData->CbteFch),
            cae: $invoiceData->CAE,
            caeExpirationDate: Carbon::createFromFormat('Ymd', $invoiceData->CAEFchVto)
        );
    }

    /**
     * Generate the next invoice
     *
     * @param InvoiceParams $request
     * @return InvoiceCreatedResponse
     * @throws Exception
     */
    public function generateNextInvoice(CreateInvoiceRequest $request): InvoiceCreatedResponse
    {
        $nextInvoiceNumber = $this->getLastInvoiceNumber($request->pointOfSale, $request->invoiceType) + 1;

        $request = $request->withInvoiceRange($nextInvoiceNumber, $nextInvoiceNumber);

        return $this->generateInvoice($request);
    }

    /**
     * Get the details of a specific invoice
     *
     * @param InvoiceType $invoiceType
     * @param int $invoiceNumber
     * @param int $pointOfSale
     * @return stdClass
     * @throws Exception
     */
    public function getInvoiceDetails(int $pointOfSale, InvoiceType|int $invoiceType, int $invoiceNumber): InvoiceDetailResponse
    {
        $invoiceType = $invoiceType instanceof InvoiceType ? $invoiceType : InvoiceType::from($invoiceType);

        $response = $this->client->FECompConsultar([
            'Auth' => $this->getAuthParams(),
            'FeCompConsReq' => [
                'CbteTipo' => $invoiceType->value,
                'CbteNro' => $invoiceNumber,
                'PtoVta' => $pointOfSale,
            ],
        ]);

        if (isset($response->FECompConsultarResult->Errors) && !empty($response->FECompConsultarResult->Errors)) {
            throw new Exception('Error fetching invoice details: ' . json_encode($response->FECompConsultarResult->Errors));
        }

        return new InvoiceDetailResponse(
            concept: InvoiceConcept::from($response->FECompConsultarResult->ResultGet->Concepto),
            identification: new Identification(
                type: IdentificationType::from($response->FECompConsultarResult->ResultGet->DocTipo),
                number: $response->FECompConsultarResult->ResultGet->DocNro,
            ),
            invoiceType: InvoiceType::from($response->FECompConsultarResult->ResultGet->CbteTipo),
            pointOfSale: $response->FECompConsultarResult->ResultGet->PtoVta,
            invoiceFrom: $response->FECompConsultarResult->ResultGet->CbteDesde,
            invoiceTo: $response->FECompConsultarResult->ResultGet->CbteHasta,
            invoiceDate: isset($response->FECompConsultarResult->ResultGet->CbteFch) && !empty($response->FECompConsultarResult->ResultGet->CbteFch)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->CbteFch)
                : null,
            totalAmount: (float)$response->FECompConsultarResult->ResultGet->ImpTotal,
            untaxedAmount: (float)($response->FECompConsultarResult->ResultGet->ImpTotConc ?? 0),
            netAmount: (float)($response->FECompConsultarResult->ResultGet->ImpNeto ?? 0),
            exemptAmount: (float)($response->FECompConsultarResult->ResultGet->ImpOpEx ?? 0),
            taxesAmount: (float)($response->FECompConsultarResult->ResultGet->ImpTrib ?? 0),
            vatAmount: (float)($response->FECompConsultarResult->ResultGet->ImpIVA ?? 0),
            serviceDateFrom: isset($response->FECompConsultarResult->ResultGet->FchServDesde) && !empty($response->FECompConsultarResult->ResultGet->FchServDesde)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchServDesde)
                : null,
            serviceDateTo: isset($response->FECompConsultarResult->ResultGet->FchServHasta) && !empty($response->FECompConsultarResult->ResultGet->FchServHasta)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchServHasta)
                : null,
            paymentDueDate: isset($response->FECompConsultarResult->ResultGet->FchVtoPago) && !empty($response->FECompConsultarResult->ResultGet->FchVtoPago)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchVtoPago)
                : null,
            currencyCode: Currency::from($response->FECompConsultarResult->ResultGet->MonId),
            currencyRate: (float)($response->FECompConsultarResult->ResultGet->MonCotiz ?? 1),
            recipientVatCondition: RecipientVatCondition::from($response->FECompConsultarResult->ResultGet->CondicionIVAReceptorId),
            relatedInvoices: collect((array)($response->FECompConsultarResult->ResultGet->CbtesAsoc->CbteAsoc ?? []))
                ->map(fn($ri) => new RelatedInvoice(
                    invoiceType: InvoiceType::from($ri->Tipo),
                    pointOfSale: $ri->PtoVta ?? 0,
                    invoiceNumber: $ri->Nro ?? 0,
                )),
            taxes: collect(array_map(fn($t) => new Tax(
                id: $t->Id,
                description: $t->Desc,
                baseAmount: $t->BaseImp,
                rate: $t->Alic,
                amount: $t->Importe
            ), (array)($response->FECompConsultarResult->ResultGet->Tributos->Tributo ?? []))),
            vatItems: collect(array_map(fn($v) => new Vat(
                id: $v->Id,
                baseAmount: $v->BaseImp,
                amount: $v->Importe
            ), (array)($response->FECompConsultarResult->ResultGet->Iva->AlicIva ?? []))),
            optionals: collect(array_map(fn($o) => new Optional(
                id: $o->Id,
                value: $o->Valor
            ), (array)($response->FECompConsultarResult->ResultGet->Opcionales->Opcional ?? []))),
            buyers: collect(array_map(fn($b) => new Buyer(
                identification: new Identification(
                    type: IdentificationType::from($b->DocTipo),
                    number: $b->DocNro,
                ),
                percentage: (float)$b->Porcentaje,
            ), (array)($response->FECompConsultarResult->ResultGet->Compradores->Comprador ?? []))),
            periodFrom: isset($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchDesde) && !empty($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchDesde)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchDesde)
                : null,
            periodTo: isset($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchHasta) && !empty($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchHasta)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchHasta)
                : null,
            result: $response->FECompConsultarResult->ResultGet->Resultado ?? '',
            authorizationCode: $response->FECompConsultarResult->ResultGet->CodAutorizacion ?? '',
            emissionType: $response->FECompConsultarResult->ResultGet->EmisionTipo ?? '',
            authorizationCodeDueDate: Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchVto),
            processDate: Carbon::createFromFormat('YmdHis', $response->FECompConsultarResult->ResultGet->FchProceso),
            observations: collect(array_map(fn($o) => new Observation(
                code: $o->Code,
                message: $o->Msg,
            ), (array)($response->FECompConsultarResult->ResultGet->Observaciones->Obs ?? [])))
        );
    }

    /**
     * Retrieve all allowed optional types
     *
     * @return Collection<OptionalTypesResponse>
     * @throws Exception
     */
    public function getOptionalTypes(): Collection
    {
        $response = $this->client->FEParamGetTiposOpcional([
            'Auth' => $this->getAuthParams(),
        ]);

        if (isset($response->FEParamGetTiposOpcionalResult->Errors) && !empty($response->FEParamGetTiposOpcionalResult->Errors)) {
            throw new Exception('Error fetching optional types: ' . json_encode($response->FEParamGetTiposOpcionalResult->Errors));
        }

        return (new Collection($response->FEParamGetTiposOpcionalResult->ResultGet->OpcionalTipo))
            ->map(fn($optionalType) => new OptionalTypesResponse(
                id: $optionalType->Id,
                description: $optionalType->Desc,
            ));
    }

    /* ---------- [ Private Methods ] ----------  */

    private function getAuthParams(): array
    {
        $authorizationTicket = $this->wsaaClient->getAuthorizationTicket(WebService::WSFE);

        return [
            'Token' => $authorizationTicket->token,
            'Sign' => $authorizationTicket->sign,
            'Cuit' => config('laravel-arca-sdk.cuit'),
        ];
    }
}