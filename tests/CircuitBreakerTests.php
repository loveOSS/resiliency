<?php

namespace Tests\PrestaShop\CircuitBreaker;

use Symfony\Component\EventDispatcher\EventDispatcher;
use PrestaShop\CircuitBreaker\Storages\SymfonyCache;
use PrestaShop\CircuitBreaker\SymfonyCircuitBreaker;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Systems\MainSystem;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use Symfony\Component\Cache\Simple\ArrayCache;

class CircuitBreakerTests extends CircuitBreakerTestCase
{
    /**
     * When we use the circuit breaker on unreachable service
     * the fallback response is used.
     *
     * @dataProvider getCircuitBreakers
     */
    public function testCircuitBreakerIsInClosedStateAtStart($circuitBreaker)
    {
        $this->assertSame('CLOSED', $circuitBreaker->getState());

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
     * @dataProvider getCircuitBreakers
     */
    public function testCircuitBreakerWillBeOpenedInCaseOfFailures($circuitBreaker)
    {
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());

        $this->assertSame('OPENED', $circuitBreaker->getState());
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
     * @dataProvider getCircuitBreakers
     */
    public function testAfterTheThresholdTheCircuitBreakerMovesInHalfOpenState($circuitBreaker)
    {
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
        $this->assertSame('HALF_OPENED', $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isHalfOpened());
    }

    /**
     * In HalfOpen state, if the service is back we can
     * close the CircuitBreaker.
     *
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenedInCaseOfFailures
     * @depends testAfterTheThresholdTheCircuitBreakerMovesInHalfOpenState
     * @dataProvider getCircuitBreakers
     */
    public function testOnceInHalfOpenModeServiceIsFinallyReachable($circuitBreaker)
    {
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
        $this->assertSame('HALF_OPENED', $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isHalfOpened());

        sleep(2);
        // CLOSED
        $this->assertSame(
            '{"hello": "world"}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );

        $this->assertSame('CLOSED', $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isClosed());
    }

    /**
     * Return the list of supported circuit breakers
     *
     * @return array
     */
    public function getCircuitBreakers()
    {
        return [
            'simple' => $this->createSimpleCircuitBreaker(),
            'symfony' => $this->createSymfonyCircuitBreaker(),
        ];
    }

    /**
     * @return SimpleCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSimpleCircuitBreaker()
    {
        return new SimpleCircuitBreaker(
            new OpenPlace(0, 0, 1), // threshold 1s
            new HalfOpenPlace(0, 0.2, 0), // timeout 0.2s to test the service
            new ClosedPlace(2, 0.2, 0), // 2 failures allowed, 0.2s timeout
            $this->getTestClient()
        );
    }

    /**
     * @return SymfonyCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSymfonyCircuitBreaker()
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );

        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcher::class);

        return new SymfonyCircuitBreaker(
            $system,
            $this->getTestClient(),
            $symfonyCache,
            $eventDispatcherS
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
