<?php

namespace AgustinZamar\LaravelArcaSdk\Clients;

use AgustinZamar\LaravelArcaSdk\Domain\Identification;
use AgustinZamar\LaravelArcaSdk\Domain\Invoice;
use AgustinZamar\LaravelArcaSdk\Domain\VatCondition;
use AgustinZamar\LaravelArcaSdk\Enums\IdentificationType;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceConcept;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Request\InvoiceParams;
use Carbon\Carbon;
use Exception;
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
     * @return Collection<VatCondition>
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
            ->map(fn($vatCondition) => new VatCondition(
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
     * @param InvoiceParams $params
     * @return Invoice
     * @throws Exception
     */
    public function generateInvoice(InvoiceParams $params): Invoice
    {
        $params = array_merge(['Auth' => $this->getAuthParams()], $params->toArray());
        $response = $this->client->FECAESolicitar($params);

        if (isset($response->FECAESolicitarResult->Errors) && !empty($response->FECAESolicitarResult->Errors)) {
            throw new Exception('Error creating invoice: ' . json_encode($response->FECAESolicitarResult->Errors));
        }

        $invoiceData = $response->FECAESolicitarResult->FeDetResp->FECAEDetResponse;

        return new Invoice(
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
     * @param InvoiceParams $params
     * @return Invoice
     * @throws Exception
     */
    public function generateNextInvoice(InvoiceParams $params): Invoice
    {
        $lastInvoiceNumber = $this->getLastInvoiceNumber($params->getPointOfSale(), $params->getInvoiceType());
        $params->setInvoiceFrom($lastInvoiceNumber + 1);
        $params->setInvoiceTo($lastInvoiceNumber + 1);

        return $this->generateInvoice($params);
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