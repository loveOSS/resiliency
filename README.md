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

```php
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;

$circuitBreaker = new SimpleCircuitBreaker(
    new OpenPlace(0, 0, 10),
    new HalfOpenPlace(0, 0.2, 0),
    new ClosedPlace(2, 0.1, 2)
);

$fallbackResponse = function () {
    return '{}';
};

$circuitBreaker->call('https://api.domain.com', $fallback);
```

## Tests

```
composer test
```

## Code quality

```
composer cs-fix && composer phpstan
```