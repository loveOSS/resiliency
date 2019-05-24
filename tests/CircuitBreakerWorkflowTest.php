<?php

namespace Tests\Resiliency;

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
        $this->assertTrue($circuitBreaker->isClosed());

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
        $this->assertTrue($circuitBreaker->isClosed());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);

        //After two failed calls switch to OPEN state
        $this->assertTrue($circuitBreaker->isOpened());
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
        $this->assertTrue($circuitBreaker->isClosed());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertTrue($circuitBreaker->isOpened());

        // OPEN - no call to client
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertTrue($circuitBreaker->isOpened());
        $this->waitFor(2 * self::OPEN_THRESHOLD);

        // SWITCH TO HALF OPEN - retry to call the service
        $this->assertSame(
            '{"hello": "world"}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
        $this->assertTrue($circuitBreaker->isClosed());
    }

    /**
     * The Circuit Breaker can be isolated, once its done it remains
     * Open and so on only fallback responses will be sent.
     *
     * @dataProvider getCircuitBreakers
     */
    public function testOnceCircuitBreakerIsIsolatedNoTrialsAreDone(CircuitBreaker $circuitBreaker): void
    {
        $circuitBreaker->isolate('https://httpbin.org/get/foo');

        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertTrue($circuitBreaker->isIsolated());

        // Let's do 10 calls!

        for ($i = 0; $i < 10; ++$i) {
            $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
            $this->assertSame('{}', $response);
            $this->assertTrue($circuitBreaker->isIsolated());
        }

        $circuitBreaker->reset('https://httpbin.org/get/foo');

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
            $this->getTestClient(),
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
        $eventDispatcherS->expects($this->any())
            ->method('dispatch')
            ->willReturn($this->createMock(Event::class))
        ;

        return new MainCircuitBreaker(
            $this->getSystem(),
            $this->getTestClient(),
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
