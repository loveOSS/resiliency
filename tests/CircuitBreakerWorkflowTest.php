<?php

namespace Tests\Resiliency;

use Resiliency\Places\ClosedPlace;
use Resiliency\Places\IsolatedPlace;
use Resiliency\Places\OpenPlace;
use Resiliency\TransitionDispatchers\SimpleDispatcher;
use Resiliency\TransitionDispatchers\SymfonyDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\EventDispatcher\Event;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Storages\SymfonyCache;
use Resiliency\Storages\SimpleArray;
use Resiliency\MainCircuitBreaker;
use org\bovigo\vfs\vfsStream;

class CircuitBreakerWorkflowTest extends CircuitBreakerTestCase
{
    /**
     * @var int the number of seconds to wait before try to reach again the service
     */
    private const OPEN_THRESHOLD = 1;

    /**
     * When we use the circuit breaker on unreachable service
     * the fallback response is used.
     *
     * @param CircuitBreaker $circuitBreaker
     * @dataProvider getCircuitBreakers
     */
    public function testCircuitBreakerIsInClosedStateAtStart(CircuitBreaker $circuitBreaker): void
    {
        $this->assertInstanceOf(ClosedPlace::class, $circuitBreaker->getState());

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
    public function testCircuitBreakerWillBeOpenInCaseOfFailures(CircuitBreaker $circuitBreaker): void
    {
        // CLOSED
        $this->assertInstanceOf(ClosedPlace::class, $circuitBreaker->getState());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);

        //After two failed calls switch to OPEN state
        $this->assertInstanceOf(OpenPlace::class, $circuitBreaker->getState());
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
    }

    /**
     * In HalfOpen state, if the service is back we can
     * close the CircuitBreaker.
     *
     * @param CircuitBreaker $circuitBreaker
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenInCaseOfFailures
     * @dataProvider getCircuitBreakers
     */
    public function testOnceInHalfOpenModeServiceIsFinallyReachable(CircuitBreaker $circuitBreaker): void
    {
        // CLOSED - first call fails (twice)
        $this->assertInstanceOf(ClosedPlace::class, $circuitBreaker->getState());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertInstanceOf(OpenPlace::class, $circuitBreaker->getState());

        // OPEN - no call to client
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertInstanceOf(OpenPlace::class, $circuitBreaker->getState());
        $this->waitFor(2 * self::OPEN_THRESHOLD);

        // SWITCH TO HALF OPEN then CLOSED - retry to call the service
        $this->assertSame(
            '{"hello": "world"}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
        $this->assertInstanceOf(ClosedPlace::class, $circuitBreaker->getState());
    }

    /**
     * The Circuit Breaker can be isolated, once its done it remains
     * Open and so on only fallback responses will be sent.
     *
     * @dataProvider getCircuitBreakers
     *
     * @param CircuitBreaker $circuitBreaker
     */
    public function testOnceCircuitBreakerIsIsolatedNoTrialsAreDone(CircuitBreaker $circuitBreaker): void
    {
        $uri = 'https://httpbin.org/get/foo';
        $circuitBreaker->call($uri, $this->createFallbackResponse());
        $circuitBreaker->isolate($uri);

        $response = $circuitBreaker->call($uri, $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertInstanceOf(IsolatedPlace::class, $circuitBreaker->getState());

        // Let's do 10 calls!

        for ($i = 0; $i < 10; ++$i) {
            $circuitBreaker->call($uri, $this->createFallbackResponse());
            $this->assertSame('{}', $response);
            $this->assertInstanceOf(IsolatedPlace::class, $circuitBreaker->getState());
        }

        $circuitBreaker->reset($uri);

        $this->assertInstanceOf(ClosedPlace::class, $circuitBreaker->getState());
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
            //'symfony' => [$this->createSymfonyCircuitBreaker()],
        ];
    }

    /**
     * @return MainCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSimpleCircuitBreaker(): MainCircuitBreaker
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('logs.txt', 0644)
            ->withContent('')
            ->at($root)
        ;

        return new MainCircuitBreaker(
            $this->getSystem(),
            new SimpleArray(),
            new SimpleDispatcher($file->url())
        );
    }

    /**
     * @return MainCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSymfonyCircuitBreaker(): MainCircuitBreaker
    {
        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcher::class);
        $eventDispatcherS->method('dispatch')
            ->willReturn($this->createMock(Event::class))
        ;

        return new MainCircuitBreaker(
            $this->getSystem(),
            $symfonyCache,
            new SymfonyDispatcher($eventDispatcherS)
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
