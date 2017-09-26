# silverstripe-nhi-field
A Silverstipe module to provide a NHI db field and NHI Form field to track NZ's "National Health Index" number.

[![Build Status](https://travis-ci.org/webtorque/silverstripe-nhi-field.svg?branch=dev)](https://travis-ci.org/webtorque/silverstripe-nhi-field)

## Features
* Custom DB field type
* Custom form field type with basic pattern validation and checksum validation.
* Consistently save NHI in all caps.

## Requirements
* PHP 5.4 or greater (tested with up to PHP 7.1)
* `silverstripe/framework:^3.0`
* `silverstripe/cms:^3.0`

## Installation
```bash
composer require webtorque/silverstripe-nhi-field:^0.0
```

## Usage
To specify that an NHI db field on a DataObject:
```php
class Patient extends DataObject {
    private static $db = [
        'NationalHealthIndex' => 'NHI',
    ];
}
```

The `NHI` field type is equivalent to `Varchar(7)`. When scaffolding a form, any NHI db field will autmatically use
`NHIField` form field instead of `TextField`.

## Validating an NHI
Just use the `NHIField` in your form and validate your Form like you would normally.

`NHIField` is a simple extension `TextField` with the following alteration:
* The `maxlength` is automatically set to 7.
* The `pattern` attribute is set to a proper regular expression. This provides some limited front end validation for modern browsers.
* When setting the value of the field, it will automatically be converted to uppercase.
* The value is validated with a basic regex and with a [checksum as specified by the NHI standard](https://en.wikipedia.org/wiki/NHI_Number#Format).

For testing purposes, checksum validation can be disabled by setting the `disable_checksum_validation` flag on the `NHIField` config.
```php
if (Director::isDev()) {
    NHIField::config()->disable_checksum_validation = true;
}
```
