<img src="https://cdn.riverty.design/logo/riverty-checkout-logo.svg" width="200">

[![Latest Stable Version](https://poser.pugx.org/riverty/riverty-sdk-php/v/stable)](https://packagist.org/packages/riverty/riverty-sdk-php)
[![Latest Unstable Version](https://poser.pugx.org/riverty/riverty-sdk-php/v/unstable)](https://packagist.org/packages/riverty/riverty-sdk-php)
[![Total Downloads](https://poser.pugx.org/riverty/riverty-sdk-php/downloads)](https://packagist.org/packages/riverty/riverty-sdk-php)
[![License](https://poser.pugx.org/riverty/riverty-sdk-php/license)](https://packagist.org/packages/riverty/riverty-sdk-php)

# Riverty PHP API client
This package is a convenience wrapper to communicate with the Riverty REST API.

## Installation
For the installation of the client, use composer.

### Composer
Include the package in your `composer.json` file
``` json
{
    "require": {
        "riverty/riverty-sdk-php": "<VERSION>"
    }
}
```

...then run `composer update` and load the composer autoloader:

``` php
<?php
require 'vendor/autoload.php';

// ...
```

## Getting started
To get started with connecting to the Riverty API, please check the Riverty developer portal (https://developer.riverty.com) for test credentials and more specific documentation on how to integrate.

## Examples
The folder Examples contains examples for all available operations.

## Documentation
More documentation can be found at [developer.riverty.com](https://developer.riverty.com)

## Contributing
We love contributions, but please note that every contribution has to be reviewed and tested. If you have suggested changes, you may create a Pull Request.

## Release notes

**2024.07.17 - version 1.2.0**

* Adjust logic for setting customerCategory element value at deliveryCustomer with B2B payments 

**2024.05.15 - version 1.1.0**

* Remove soap client object initialization and force only rest client initialization  

**2024.05.10 - version 1.0.0**

* Update naming conventions from 'AfterPay' to 'Riverty'  
* Removing Soap related components
* Migration of old php Library to new library supporting only Restful features