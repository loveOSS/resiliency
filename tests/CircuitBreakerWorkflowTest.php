<?php

namespace Tests\Resiliency;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Storages\SymfonyCache;
use Resiliency\SymfonyCircuitBreaker;
use Resiliency\SimpleCircuitBreaker;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Cache\Simple\ArrayCache;

class CircuitBreakerWorkflowTest extends CircuitBreakerTestCase
{
    /**
     * When we use the circuit breaker on unreachable service
     * the fallback response is used.
     *
     * @param CircuitBreaker $circuitBreaker
     * @dataProvider getCircuitBreakers
     */
    public function testCircuitBreakerIsInClosedStateAtStart(CircuitBreaker $circuitBreaker): void
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
     * @param CircuitBreaker $circuitBreaker
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @dataProvider getCircuitBreakers
     */
    public function testCircuitBreakerWillBeOpenedInCaseOfFailures(CircuitBreaker $circuitBreaker): void
    {
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());

        $this->assertSame('OPEN', $circuitBreaker->getState());
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
     * @param CircuitBreaker $circuitBreaker
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenedInCaseOfFailures
     * @dataProvider getCircuitBreakers
     */
    public function testAfterTheThresholdTheCircuitBreakerMovesInHalfOpenState(CircuitBreaker $circuitBreaker): void
    {
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        // OPEN
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());

        $this->waitFor(1);
        // NOW HALF OPEN
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
        $this->assertSame('HALF OPEN', $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isHalfOpened());
    }

    /**
     * In HalfOpen state, if the service is back we can
     * close the CircuitBreaker.
     *
     * @param CircuitBreaker $circuitBreaker
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenedInCaseOfFailures
     * @depends testAfterTheThresholdTheCircuitBreakerMovesInHalfOpenState
     * @dataProvider getCircuitBreakers
     */
    public function testOnceInHalfOpenModeServiceIsFinallyReachable(CircuitBreaker $circuitBreaker): void
    {
        // CLOSED
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        // OPEN
        $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());

        $this->waitFor(1);
        // NOW HALF OPEN
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
        $this->assertSame('HALF OPEN', $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isHalfOpened());

        $this->waitFor(1);

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
    public function getCircuitBreakers(): array
    {
        return [
             'simple' => [$this->createSimpleCircuitBreaker()],
            'symfony' => [$this->createSymfonyCircuitBreaker()],
        ];
    }

    /**
     * @return SimpleCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSimpleCircuitBreaker(): SimpleCircuitBreaker
    {
        return new SimpleCircuitBreaker($this->getSystem(), $this->getTestClient());
    }

    /**
     * @return SymfonyCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSymfonyCircuitBreaker(): SymfonyCircuitBreaker
    {
        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcher::class);
        $eventDispatcherS->expects($this->any())
            ->method('dispatch')
            ->willReturn($this->createMock(Event::class))
        ;

        return new SymfonyCircuitBreaker(
            $this->getSystem(),
            $this->getTestClient(),
            $symfonyCache,
            $eventDispatcherS
        );
    }

    /**
     * @return callable the fallback callable
     */
    private function createFallbackResponse(): callable
    {
        return function () {
            return '{}';
        };
    }
}
