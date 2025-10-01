# A Laravel package to easily use the ARCA (ex AFIP) web services.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/agustinzamar/laravel-arca-sdk.svg?style=flat-square)](https://packagist.org/packages/agustinzamar/laravel-arca-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/agustinzamar/laravel-arca-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/agustinzamar/laravel-arca-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/agustinzamar/laravel-arca-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/agustinzamar/laravel-arca-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/agustinzamar/laravel-arca-sdk.svg?style=flat-square)](https://packagist.org/packages/agustinzamar/laravel-arca-sdk)

This package makes it easy to use the ARCA (ex AFIP) web services in your Laravel application.

⚠️ Do not use this package yet, it is still under development.

## Installation

You can install the package via composer:

```bash
composer require agustinzamar/laravel-arca-sdk
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="arca-sdk-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="arca-sdk-views"
```

### Setup

To Start using the ARCA Web Services you need to register a certificate on their system.

1. Create CSR file:
    2. Run `openssl genrsa -out arca.key 2048`
    3. Run
       `openssl req -new -key arca.key -subj "/C=AR/O=YourName/CN=AppName/serialNumber=CUIT XXXXXXXXXXX" -out arca.csr`
2. Login into [ARCA](https://www.afip.gob.ar/) using your CUIT and password
3. Search for WSAS - Autogestión Certificados Homologación"
4. Select "Nuevo Certificado"
5. Set a name and paste the contents of the arca.csr file, then click on "Crear DN y obtener certificado"
6. Copy the result on a plain text file and save it as arca.crt
7. Paste `arca.key`, `arca.csr`, and `arca.crt` in `storage/app/arca/`
8. You are now ready to use the package

## Usage

You can use the package by using the `Arca` facade. Here is an example of how to use it:

### Obtaining Invoice Types

```php
Arca::getInvoiceTypes()
```

### Obtaining Recipient VAT Conditions

```php
Arca::getRecipientVatConditions();
```

### Obtaining the last authorized invoice number

```php
$pointOfSale = 1; // Your point of sale number
Arca::getLastInvoiceNumber($pointOfSale, InvoiceType::FACTURA_A);
```

### Obtaining the details of an existing invoice

```php
$pointOfSale = 1; // Your point of sale number
$invoiceNumber = 123; // Invoice number to query
Arca::getInvoiceDetails($pointOfSale, InvoiceType::FACTURA_C, $invoiceNumber);
```

### Creating an invoice

```php
$pointOfSale = 1; // Your point of sale number
$nextInvoiceNumber = Arca::getLastInvoiceNumber($pointOfSale, InvoiceType::FACTURA_C); 
$request = new CreateInvoiceRequest(
    concept: InvoiceConcept::GOODS,
    pointOfSale: $pointOfSale,
    identification: new Identification(
        type: IdentificationType::CUIT,
        number: 20111111112
    ),
    invoiceType: InvoiceType::FACTURA_C,
    invoiceFrom: $nextInvoiceNumber,
    invoiceTo: $nextInvoiceNumber,
    total: 150.0,
    net: 150.0,
    exempt: 0.0,
    nonTaxableConceptsAmount: 0.0,
    vatCondition: 1,
    currency: Currency::ARS,
    currencyQuote: 1.0,
    invoiceDate: now()->addDays(3),
);
        
Arca::generateInvoice($request);

// Or automatically generate next invoice
$request = new CreateInvoiceRequest(
    concept: InvoiceConcept::GOODS,
    pointOfSale: 12,
    identification: new Identification(
        type: IdentificationType::CUIT,
        number: 20111111112
    ),
    invoiceType: InvoiceType::FACTURA_C,
    total: 150.0,
    net: 150.0,
    exempt: 0.0,
    nonTaxableConceptsAmount: 0.0,
    vatCondition: 1,
    currency: Currency::ARS,
    currencyQuote: 1.0,
    invoiceDate: now()->addDays(3),
);
        
Arca::generateNextInvoice($request);
```

The package also offers a set of convenient Enums for commonly used values:

- `InvoiceType`: Represents the different types of invoices (e.g., FACTURA_A, FACTURA_B, etc.).
- `InvoiceConcept`: Represents the concept of the invoice (e.g., GOODS, SERVICES, etc.).
- `IdentificationType`: Represents the type of identification (e.g., CUIT, CUIL, etc.).
- `Currency`: Represents the currency type (e.g., ARS, USD, etc.).
- `RecipientVatCondition`: Represents the VAT condition of the recipient (e.g., RESPONSABLE_INSCRIPTO, MONOTRIBUTISTA,
  etc.).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please report any security vulnerabilities to agustinzamar33@gmail.com

## Credits

- [Agustin Zamar](https://github.com/agustinzamar)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
