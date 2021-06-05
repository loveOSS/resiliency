# Resiliency, an implementation for resilient and modern PHP applications

[![codecov](https://codecov.io/gh/loveOSS/resiliency/branch/master/graph/badge.svg)](https://codecov.io/gh/loveOSS/resiliency) [![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/) [![Psalm](https://img.shields.io/badge/Psalm-Level%20Max-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/) [![Build Status](https://travis-ci.com/loveOSS/resiliency.svg?branch=master)](https://travis-ci.com/loveOSS/resiliency) 

## Main principles

![circuit breaker](https://user-images.githubusercontent.com/1247388/49721725-438bd700-fc63-11e8-8498-82ca681b15fb.png)

This library is compatible with PHP 7.4+.

## Installation

```
composer require love-oss/resiliency
```

## Use

You need to configure a system for the Circuit Breaker:

* the **failures**: define how many times we try to access the service;
* the **timeout**: define how long we wait (in ms) before consider the service unreachable;
* the **stripped timeout**: define how long we wait (in ms) before consider the service unreachable, once we're in half open state;
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
    'threshold' => 10000,
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

Resiliency library supports both [Guzzle (v6 & v7)](http://docs.guzzlephp.org/en/stable/index.html) and HttpClient Component from [Symfony (v4 & v5)](https://symfony.com/doc/current/components/http_client.html).

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
composer cs-fix && composer phpstan && composer psalm && composer phpqa
```

We also use [PHPQA](https://github.com/EdgedesignCZ/phpqa#phpqa) to check the Code quality
during the CI management of the contributions:

```
composer phpqa
```

 ## I've heard about the PrestaShop Circuit Breaker library : what library should I chose ?
 
 Welcome, that's an interesting question !
 
 Above all, I must say that I'm the former author of the PrestaShop [Circuit Breaker](https://github.com/PrestaShop/circuit-breaker) library
 and I have decided to fork my own library to be able to improve it without the constraints of the PrestaShop CMS main project.
 
 As of now (June, 2021), the both libraries have a lot in common !
 
The share almost the same API, but the PrestaShop Core Team have created their own implementations of [Circuit Breaker interface](https://github.com/PrestaShop/circuit-breaker/blob/develop/src/AdvancedCircuitBreaker.php) and [Factory](https://github.com/PrestaShop/circuit-breaker/blob/develop/src/AdvancedCircuitBreakerFactory.php) :

* SimpleCircuitBreaker
* AdvancedCircuitBreaker
* PartialCircuitBreaker
* SymfonyCircuitBreaker


1. They maintain a version compatible with PHP 7.2+ and Symfony 4 but not (yet ?) with PHP 8 and Symfony 5 ;
2. They have a dependency on their own package named [php-dev-tools](https://github.com/PrestaShop/php-dev-tools) ;
3. They maintain an implementation of [Storage](https://github.com/PrestaShop/circuit-breaker/blob/v4.0.0/src/Storage/DoctrineCache.php) using Doctrine Cache library ;
4. They don't have a Symfony HttpClient implementation ;
5. For the events, I'm not sure as their implementation make the list difficult to establish ;
6. They don't provide a mecanism to reset and restore a Circuit Breaker ;
7. They don't provide a mecanism to monitor the activity of a Circuit Breaker ;
8. They have removed [Psalm](https://psalm.dev/) from their CI and they don't use [PHPQA](https://github.com/EdgedesignCZ/phpqa) ;
9. They have added `declare(strict_types=1);` on all the files (which is useless in Resiliency as there is no situation where PHP could try to cast) ;
10. They don't declare a `.gitattributes` file, so I guess all tests are downloaded when [we require](https://madewithlove.com/blog/software-engineering/gitattributes/) their library ;

> All right ... but this don't tell me what library should I use in my project !

* If you need PHP 5.6, use Circuit Breaker v3
* If you need PHP 7.2, use Circuit Breaker v4
* If you need PHP 7.4+, use Resiliency
* If you need a library maintained by a team of developers, use PrestaShop
* If you trust [me](https://github.com/mickaelandrieu) to maintain this package _almost_ all alone, use Resiliency !
