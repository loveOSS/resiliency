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
    /**
     * @todo: imho the circuit breaker shouldn't returns null
     * in closed state, needs more information.
     */
    public function testCircuitBreakerIsInClosedStateAtStart()
    {
        $circuitBreaker = $this->createCircuitBreaker();

        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
        $this->assertNull($circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallback()));
    }

    /**
     * @depends testCircuitBreakerIsInClosedStateAtStart
     */
    public function testCircuitBreakerWillBeOpenedInCaseOfFailures()
    {
        $circuitBreaker = $this->createCircuitBreaker();
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallback());

        $this->assertSame(States::OPEN_STATE, $circuitBreaker->getState());
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallback()
            )
        );
    }

    /**
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenedInCaseOfFailures
     */
    public function testAfterTheThresholdTheCircuitBreakerMovesInHalfOpenState()
    {
        $circuitBreaker = $this->createCircuitBreaker();
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallback());
        // OPEN
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallback());

        sleep(2);
        // NOW HALF OPEN
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallback()
            )
        );
        $this->assertSame(States::HALF_OPEN_STATE, $circuitBreaker->getState());
    }

    /**
     * @return SimpleCircuitBreaker the circuit breaker for testing purposes
     */
    private function createCircuitBreaker()
    {
        return new SimpleCircuitBreaker(
            new OpenPlace(0, 0, 1), // threshold 1s
            new HalfOpenPlace(0, 0.2, 0), // timeout 0.2s to test the service
            new ClosedPlace(2, 0.2, 0) // 2 failures allowed, 0.2s timeout
        );
    }

    /**
     * @return callable the fallback callable
     */
    private function createFallback()
    {
        return function () {
            return '{}';
        };
    }
}
