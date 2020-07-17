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

You need to configure a system for the Circuit Breaker:

* the **failures**: define how much times we try to access the service;
* the **timeout**: define how long we wait (in ms) before consider the service unreachable;
* the **striped timeout**: define how long we wait (in ms) before consider the service unreachable, once we're in half open state;
* the **threshold**: define how long we wait (in ms) before trying to access again the service;
* the (HTTP|HTTPS) **client** that will be used to reach the services;
* the **fallback** callback will be used if the distant service is unreachable when the Circuit Breaker is Open (means "is used"). 

> You'd better return the same type of response expected from your distant call.

```php
use Resiliency\MainCircuitBreaker;
use Resiliency\Systems\MainSystem;
use Resiliency\Storages\SimpleArray;
use Resiliency\Clients\SymfonyClient;
use Symfony\Component\HttpClient\HttpClient;

$client = new SymfonyClient(HttpClient::create());

$mainSystem = MainSystem::createFromArray([
    'failures' => 2,
    'timeout' => 100,
    'stripped_timeout' => 200,
    'threshold' => 10.0,
], $client);

$storage = new SimpleArray();

// Any PSR-14 Event Dispatcher implementation.
$dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;

$circuitBreaker = new MainCircuitBreaker(
    $mainSystem,
    $storage,
    $dispatcher
);

/**
 * @var Service $service
 */
$fallbackResponse = function ($service) {
    return '{}';
};

$circuitBreaker->call(
    'https://api.domain.com',
    $fallbackResponse,
    [
        'query' => [
            '_token' => '123456789',
        ]
    ]
);
```

### Clients

Since v0.6, Resiliency library supports both Guzzle 6 and HttpClient Component from Symfony.

> For the Guzzle implementation, the Client options are described
> in the [HttpGuzzle documentation](http://docs.guzzlephp.org/en/stable/index.html).

> For the Symfony implementation, the Client options are described
> in the [HttpClient Component documentation](https://symfony.com/doc/current/components/http_client.html).

### Monitoring

This library provides a minimalist system to help you monitor your circuits.

```php
$monitor = new SimpleMonitor();

// Collect information while listening
// to some circuit breaker events...
function listener(Event $event) {
    $monitor->add($event);
};

// Retrieve a complete report for analysis or storage
$report = $monitor->getReport();
```

## Tests

```
composer test
```

## Code quality

This library has high quality standards:

```
composer cs-fix && composer phpstan && composer psalm
```

We also use [PHPQA](https://github.com/EdgedesignCZ/phpqa#phpqa) to check the Code quality
during the CI management of the contributions:

```
composer phpqa
```
