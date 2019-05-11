# Resiliency, an implementation for resilient PHP 7 applications

[![codecov](https://codecov.io/gh/loveOSS/resiliency/branch/master/graph/badge.svg)](https://codecov.io/gh/loveOSS/resiliency) [![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/) [![Psalm](https://img.shields.io/badge/Psalm-Level%20Max-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/) [![Build Status](https://travis-ci.com/loveOSS/resiliency.svg?branch=master)](https://travis-ci.com/loveOSS/resiliency) 

## Main principles

![circuit breaker](https://user-images.githubusercontent.com/1247388/49721725-438bd700-fc63-11e8-8498-82ca681b15fb.png)

This library is compatible with PHP 7.2+.

## Installation

```
composer require love-oss/resiliency
```

## Use

You can use the factory to create a simple circuit breaker.

By default, you need to define 3 parameters for each "state/place" of
the circuit breaker:

* the **failures**: define how much times we try to access the service;
* the **timeout**: define how much time we wait before consider the service unreachable;
* the **threshold**: define how much time we wait before trying to access again the service;

We also need to define which (HTTP) client will be used to call the service.

The **fallback** callback will be used if the distant service is unreachable when the Circuit Breaker is Open (means "is used"). 

> You'd better return the same type of response expected from your distant call.

```php
use Resiliency\SimpleCircuitBreakerFactory;

$circuitBreakerFactory = new SimpleCircuitBreakerFactory();
$circuitBreaker = $circuitBreakerFactory->create(
    [
        'closed' => [2, 0.1, 0.1],
        'open' => [0, 0.0, 10.0],
        'half_open' => [1, 0.2, 0.0],
        'client' => [
            'proxy' => '192.168.16.1:10',
            'method' => 'POST',
        ],
    ]
);

$fallbackResponse = function () {
    return '{}';
};

$circuitBreaker->call(
    'https://api.domain.com',
    $fallbackResponse,
    [
        '_token' => '123456789',
    ]
);
```

> For the Guzzle implementation, the Client options are described
> in the [HttpGuzzle documentation](http://docs.guzzlephp.org/en/stable/index.html).

## Tests

```
composer test
```

## Code quality

```
composer cs-fix && composer phpstan && composer psalm
```

We also use [PHPQA](https://github.com/EdgedesignCZ/phpqa#phpqa) to check the Code quality
during the CI management of the contributions:

```
composer phpqa
```
