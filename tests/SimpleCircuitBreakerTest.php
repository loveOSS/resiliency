<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\States;

/**
 * @todo: needs tools to emulate a service unreachable
 * and then back again.
 */
class SimpleCircuitBreakerTest extends TestCase
{
    /**
     * When we use the circuit breaker on unreachable service
     * the fallback response is used.
     */
    public function testCircuitBreakerIsInClosedStateAtStart()
    {
        $circuitBreaker = $this->createCircuitBreaker();

        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());

        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
    }

    /**
     * Once the number of failures is reached, the circuit breaker
     * is opened. This time no calls to the services are done.
     *
     * @depends testCircuitBreakerIsInClosedStateAtStart
     */
    public function testCircuitBreakerWillBeOpenedInCaseOfFailures()
    {
        $circuitBreaker = $this->createCircuitBreaker();
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());

        $this->assertSame(States::OPEN_STATE, $circuitBreaker->getState());
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
    }

    /**
     * Once the threshold is reached, the circuit breaker
     * try again to reach the service. This time, the service
     * is not reachable.
     *
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenedInCaseOfFailures
     */
    public function testAfterTheThresholdTheCircuitBreakerMovesInHalfOpenState()
    {
        $circuitBreaker = $this->createCircuitBreaker();
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        // OPEN
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());

        sleep(2);
        // NOW HALF OPEN
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
        $this->assertSame(States::HALF_OPEN_STATE, $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isHalfOpened());
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
    private function createFallbackResponse()
    {
        return function () {
            return '{}';
        };
    }
}
