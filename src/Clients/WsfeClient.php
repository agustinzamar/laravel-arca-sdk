<?php

namespace AgustinZamar\LaravelArcaSdk\Clients;

use AgustinZamar\LaravelArcaSdk\Contracts\Request\CreateInvoiceRequest;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceCreatedResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceDetailResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceTypeResponse;
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
use AgustinZamar\LaravelArcaSdk\Exceptions\ArcaException;
use AgustinZamar\LaravelArcaSdk\Support\ArcaErrors;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use stdClass;

class WsfeClient extends ArcaClient
{
    public function __construct(WsaaClient $wsaaClient, array $options = [])
    {
        parent::__construct($wsaaClient, WebService::WSFE, $options);
    }

    /**
     * Obtain all the recipient VAT conditions
     *
     * @return Collection<RecipientVatCondition>|ArcaErrors
     *
     * @throws ArcaException
     */
    public function getRecipientVatConditions(): ArcaErrors|Collection
    {
        $response = $this->call('FEParamGetCondicionIvaReceptor');

        if ($this->hasErrors($response->FEParamGetCondicionIvaReceptorResult)) {
            return $this->handleErrorResponse($response->FEParamGetCondicionIvaReceptorResult);
        }

        return collect($response->FEParamGetCondicionIvaReceptorResult->ResultGet->CondicionIvaReceptor)
            ->map(fn ($vatCondition) => new VatConditionResponse(
                id: $vatCondition->Id,
                name: $vatCondition->Desc,
            ));
    }

    /**
     * Collection of all the points of sale which are enabled for Web Services usage
     *
     * @throws ArcaException
     */
    public function getPointsOfSale(): stdClass|ArcaErrors
    {
        $response = $this->call('FEParamGetPtosVenta');

        if ($this->hasErrors($response->FEParamGetPtosVentaResult)) {
            return $this->handleErrorResponse($response->FEParamGetPtosVentaResult);
        }

        return $response->FEParamGetPtosVentaResult->ResultGet;
    }

    /**
     * @throws ArcaException
     */
    public function getLastInvoiceNumber(int $pointOfSale, InvoiceType|int $invoiceType): int|ArcaErrors
    {
        $invoiceType = $invoiceType instanceof InvoiceType ? $invoiceType->value : $invoiceType;

        $response = $this->call('FECompUltimoAutorizado', [
            'PtoVta' => $pointOfSale,
            'CbteTipo' => $invoiceType,
        ]);

        if ($this->hasErrors($response->FECompUltimoAutorizadoResult)) {
            $this->handleErrorResponse($response->FECompUltimoAutorizadoResult);
        }

        return (int) $response->FECompUltimoAutorizadoResult->CbteNro;
    }

    /**
     * Create an invoice with the given parameters
     *
     *
     * @throws ArcaException
     */
    public function generateInvoice(CreateInvoiceRequest $request): InvoiceCreatedResponse|ArcaErrors
    {
        $response = $this->call('FECAESolicitar', $request->toArray());

        if ($this->hasErrors($response->FECAESolicitarResult)) {
            return $this->handleErrorResponse($response->FECAESolicitarResult);
        }

        $invoiceData = $response->FECAESolicitarResult->FeDetResp->FECAEDetResponse;

        return new InvoiceCreatedResponse(
            result: InvoiceCreatedResult::from($invoiceData->Resultado),
            concept: InvoiceConcept::from($invoiceData->Concepto),
            identification: new Identification(
                type: IdentificationType::from($invoiceData->DocTipo),
                number: $invoiceData->DocNro,
            ),
            invoiceFrom: $invoiceData->CbteDesde,
            invoiceTo: $invoiceData->CbteHasta,
            invoiceDate: Carbon::createFromFormat('Ymd', $invoiceData->CbteFch),
            cae: $invoiceData->CAE,
            caeExpirationDate: $invoiceData->CAEFchVto ? Carbon::createFromFormat('Ymd', $invoiceData->CAEFchVto) : null
        );
    }

    /**
     * Generate the next invoice
     *
     *
     * @throws ArcaException
     */
    public function generateNextInvoice(CreateInvoiceRequest $request): InvoiceCreatedResponse|ArcaErrors
    {
        $nextInvoiceNumber = $this->getLastInvoiceNumber($request->pointOfSale, $request->invoiceType) + 1;

        $request = $request->withInvoiceRange($nextInvoiceNumber, $nextInvoiceNumber);

        return $this->generateInvoice($request);
    }

    /**
     * Get the details of a specific invoice
     *
     *
     * @throws ArcaException
     */
    public function getInvoiceDetails(int $pointOfSale, InvoiceType|int $invoiceType, int $invoiceNumber): InvoiceDetailResponse
    {
        $invoiceType = $invoiceType instanceof InvoiceType ? $invoiceType : InvoiceType::from($invoiceType);

        $response = $this->call('FECompConsultar', [
            'FeCompConsReq' => [
                'CbteTipo' => $invoiceType->value,
                'CbteNro' => $invoiceNumber,
                'PtoVta' => $pointOfSale,
            ],
        ]);

        if ($this->hasErrors($response->FECompConsultarResult)) {
            $this->handleErrorResponse($response->FECompConsultarResult);
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
            invoiceDate: isset($response->FECompConsultarResult->ResultGet->CbteFch) && ! empty($response->FECompConsultarResult->ResultGet->CbteFch)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->CbteFch)
                : null,
            totalAmount: (float) $response->FECompConsultarResult->ResultGet->ImpTotal,
            untaxedAmount: (float) ($response->FECompConsultarResult->ResultGet->ImpTotConc ?? 0),
            netAmount: (float) ($response->FECompConsultarResult->ResultGet->ImpNeto ?? 0),
            exemptAmount: (float) ($response->FECompConsultarResult->ResultGet->ImpOpEx ?? 0),
            taxesAmount: (float) ($response->FECompConsultarResult->ResultGet->ImpTrib ?? 0),
            vatAmount: (float) ($response->FECompConsultarResult->ResultGet->ImpIVA ?? 0),
            serviceDateFrom: isset($response->FECompConsultarResult->ResultGet->FchServDesde) && ! empty($response->FECompConsultarResult->ResultGet->FchServDesde)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchServDesde)
                : null,
            serviceDateTo: isset($response->FECompConsultarResult->ResultGet->FchServHasta) && ! empty($response->FECompConsultarResult->ResultGet->FchServHasta)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchServHasta)
                : null,
            paymentDueDate: isset($response->FECompConsultarResult->ResultGet->FchVtoPago) && ! empty($response->FECompConsultarResult->ResultGet->FchVtoPago)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchVtoPago)
                : null,
            currencyCode: Currency::from($response->FECompConsultarResult->ResultGet->MonId),
            currencyRate: (float) ($response->FECompConsultarResult->ResultGet->MonCotiz ?? 1),
            recipientVatCondition: RecipientVatCondition::from($response->FECompConsultarResult->ResultGet->CondicionIVAReceptorId),
            relatedInvoices: collect((array) ($response->FECompConsultarResult->ResultGet->CbtesAsoc->CbteAsoc ?? []))
                ->map(fn ($ri) => new RelatedInvoice(
                    invoiceType: InvoiceType::from($ri->Tipo),
                    pointOfSale: $ri->PtoVta ?? 0,
                    invoiceNumber: $ri->Nro ?? 0,
                )),
            taxes: collect(array_map(fn ($t) => new Tax(
                id: $t->Id,
                description: $t->Desc,
                baseAmount: $t->BaseImp,
                rate: $t->Alic,
                amount: $t->Importe
            ), (array) ($response->FECompConsultarResult->ResultGet->Tributos->Tributo ?? []))),
            vatItems: collect(array_map(fn ($v) => new Vat(
                id: $v->Id,
                baseAmount: $v->BaseImp,
                amount: $v->Importe
            ), (array) ($response->FECompConsultarResult->ResultGet->Iva->AlicIva ?? []))),
            optionals: collect(array_map(fn ($o) => new Optional(
                id: $o->Id,
                value: $o->Valor
            ), (array) ($response->FECompConsultarResult->ResultGet->Opcionales->Opcional ?? []))),
            buyers: collect(array_map(fn ($b) => new Buyer(
                identification: new Identification(
                    type: IdentificationType::from($b->DocTipo),
                    number: $b->DocNro,
                ),
                percentage: (float) $b->Porcentaje,
            ), (array) ($response->FECompConsultarResult->ResultGet->Compradores->Comprador ?? []))),
            periodFrom: isset($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchDesde) && ! empty($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchDesde)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchDesde)
                : null,
            periodTo: isset($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchHasta) && ! empty($response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchHasta)
                ? Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->PeriodoAsoc->FchHasta)
                : null,
            result: $response->FECompConsultarResult->ResultGet->Resultado ?? '',
            authorizationCode: $response->FECompConsultarResult->ResultGet->CodAutorizacion ?? '',
            emissionType: $response->FECompConsultarResult->ResultGet->EmisionTipo ?? '',
            authorizationCodeDueDate: Carbon::createFromFormat('Ymd', $response->FECompConsultarResult->ResultGet->FchVto),
            processDate: Carbon::createFromFormat('YmdHis', $response->FECompConsultarResult->ResultGet->FchProceso),
            observations: collect(array_map(fn ($o) => new Observation(
                code: $o->Code,
                message: $o->Msg,
            ), (array) ($response->FECompConsultarResult->ResultGet->Observaciones->Obs ?? [])))
        );
    }

    /**
     * Retrieve all allowed optional types
     *
     * @return Collection<OptionalTypesResponse>
     *
     * @throws ArcaException
     */
    public function getOptionalTypes(): Collection
    {
        $response = $this->call('FEParamGetTiposOpcional');

        if ($this->hasErrors($response->FEParamGetTiposOpcionalResult)) {
            $this->handleErrorResponse($response->FEParamGetTiposOpcionalResult);
        }

        return (new Collection($response->FEParamGetTiposOpcionalResult->ResultGet->OpcionalTipo))
            ->map(fn ($optionalType) => new OptionalTypesResponse(
                id: $optionalType->Id,
                description: $optionalType->Desc,
            ));
    }

    /**
     * Retrieve invoice types
     *
     * @return Collection<InvoiceTypeResponse>
     *
     * @throws ArcaException
     */
    public function getInvoiceTypes(): Collection
    {
        $response = $this->call('FEParamGetTiposCbte');

        if ($this->hasErrors($response->FEParamGetTiposCbteResult)) {
            $this->handleErrorResponse($response->FEParamGetTiposCbteResult);
        }

        return (new Collection($response->FEParamGetTiposCbteResult->ResultGet->CbteTipo))
            ->map(fn ($invoiceType) => new InvoiceTypeResponse(
                id: $invoiceType->Id,
                name: $invoiceType->Desc,
            ));
    }
}
