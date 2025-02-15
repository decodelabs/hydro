# Hydro

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/hydro?style=flat)](https://packagist.org/packages/decodelabs/hydro)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/hydro.svg?style=flat)](https://packagist.org/packages/decodelabs/hydro)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/hydro.svg?style=flat)](https://packagist.org/packages/decodelabs/hydro)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/hydro/integrate.yml?branch=develop)](https://github.com/decodelabs/hydro/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/hydro?style=flat)](https://packagist.org/packages/decodelabs/hydro)

### Simple HTTP client wrapper around Guzzle

Hydro provides a simple interface to common HTTP client functionality using Guzzle under the hood.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---

## Installation

Install via Composer:

```bash
composer require decodelabs/hydro
```

## Usage

Basic usage for different types of files:

```php
use DecodeLabs\Hydro;

$memoryFile = Hydro::get('https://example.com/file.txt'); // Atlas file
$string = Hydro::getString('https://example.com/file.txt'); // String
$file = Hydro::getFile('https://example.com/file.txt', '/path/to/save/file.txt'); // Local file
$tempFile = Hydro::getTempFile('https://example.com/file.txt'); // Temp file
$json = Hydro::getJson('https://example.com/file.json'); // Decoded JSON array
$tree = Hydro::getJsonTree('https://example.com/file.json'); // Decoded JSON Collections/Tree
```

### Options

Pass an array of options (including URL) to the underlying client:

```php
Hydro::get([
    'url' => 'https://example.com/file.txt',
    'timeout' => 10
]);
```

### Errors

Handle error status responses (or return alternative response):

```php
$file = Hydro::get('https://example.com/file.txt', function($response) {
    switch($response->getStatusCode()) {
        case 404:
            throw Exceptional::Notfound(
                message: 'File not found'
            );

        case 500:
            throw Exceptional::Runtime(
                message: 'Server error'
            );

        default:
            return Hydro::request('GET', 'https://example.com/other.txt');
    }
});
```

## Licensing

Hydro is licensed under the proprietary License. See [LICENSE](./LICENSE) for the full license text.
