# Circuit Breaker, an implementation for resilient PHP applications

> Experimental, don't use it yet!

## Main principles

![circuit breaker](https://user-images.githubusercontent.com/1247388/49721725-438bd700-fc63-11e8-8498-82ca681b15fb.png)

This library is compatible with PHP 5.6+ and rely on Guzzle 6.

## Installation

```
composer install prestashop/circuit-breaker
```

## Use

You can use the factory to create a simple circuit breaker.

By default, you need to define 3 parameters for each "state/place" of
the circuit breaker:

* the **failures**: define how much times we try to access the service;
* the **timeout**: define how much time we wait before consider the service unreachable;
* the **threshold**: define how much time we wait before trying to access again the service;

The **fallback** callback will be used if the distant service is unreachable when the Circuit Breaker is Open (means "is used"). 

> You'd better return the same type of response expected from your distant call.

```php
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;

$circuitBreakerFactory = new SimpleCircuitBreakerFactory();
$circuitBreaker = $circuitBreakerFactory->create(
    [
        'closed' => [2, 0.1, 0],
        'open' => [0, 0, 10],
        'half_open' => [1, 0.2, 0],
    ]
);

$fallbackResponse = function () {
    return '{}';
};

$circuitBreaker->call('https://api.domain.com', $fallbackResponse);
```

## Tests

```
composer test
```

## Code quality

```
composer cs-fix && composer phpstan
```