<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\States;
use PHPUnit\Framework\TestCase;

class SimpleCircuitBreakerTest extends TestCase
{
    public function testWorkInProgress()
    {
        $circuitBreaker = new SimpleCircuitBreaker(
            new OpenPlace(0, 0, 2), // threshold 2s
            new HalfOpenPlace(0, 0.2, 0), // timeout 0.2s to test the service
            new ClosedPlace(2, 0.2, 0) // 2 failures allowed, 0.2s timeout
        );

        $fallback = function () {
            return '{}';
        };
        // First, the circuit breaker is closed
        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
        $this->assertNull($circuitBreaker->call('https://httpbin.org/get/foo', $fallback));

        // now it's OPEN, we receive a fallback response
        $this->assertSame(States::OPEN_STATE, $circuitBreaker->getState());
        $this->assertSame('{}', $circuitBreaker->call('https://httpbin.org/get/foo', $fallback));
        // wait for 2 secondes => State should become Half Open on next call
        sleep(2);
        $this->assertSame('{}', $circuitBreaker->call('https://httpbin.org/get/foo', $fallback));
        $this->assertSame(States::HALF_OPEN_STATE, $circuitBreaker->getState());
    }
}
