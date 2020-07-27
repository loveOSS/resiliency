<?php

namespace Tests\Resiliency;

use Psr\EventDispatcher\EventDispatcherInterface;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Exception;
use Resiliency\Exceptions\InvalidSystem;
use Resiliency\MainCircuitBreaker;
use Resiliency\Places\Closed;
use Resiliency\Places\Isolated;
use Resiliency\Places\Opened;
use Resiliency\Storages\SymfonyCache;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class CircuitBreakerWorkflowTest extends CircuitBreakerTestCase
{
    /**
     * @var int the number of seconds to wait before try to reach again the service
     */
    private const OPEN_THRESHOLD = 1;

    /**
     * @var CircuitBreaker the circuit breaker
     */
    private $circuitBreaker;

    /**
     * When we use the circuit breaker on unreachable service
     * the fallback response is used.
     */
    public function testCircuitBreakerIsInClosedStateAtStart(): void
    {
        self::assertInstanceOf(Closed::class, $this->circuitBreaker->getState());

        self::assertSame(
            '{"uri": https://httpbin.org/get/foo"}',
            $this->circuitBreaker->call(
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
    public function testCircuitBreakerWillBeOpenInCaseOfFailures(): void
    {
        // CLOSED
        self::assertInstanceOf(Closed::class, $this->circuitBreaker->getState());
        $response = $this->circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        self::assertSame('{"uri": https://httpbin.org/get/foo"}', $response);

        //After two failed calls switch to OPEN state
        self::assertInstanceOf(Opened::class, $this->circuitBreaker->getState());
        self::assertSame(
            '{"uri": https://httpbin.org/get/foo"}',
            $this->circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
    }

    /**
     * In HalfOpen state, if the service is back we can
     * close the CircuitBreaker.
     *
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenInCaseOfFailures
     *
     * @throws Exception
     */
    public function testOnceInHalfOpenModeServiceIsFinallyReachable(): void
    {
        // CLOSED - first call fails (twice)
        self::assertInstanceOf(Closed::class, $this->circuitBreaker->getState());
        $response = $this->circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        self::assertSame('{"uri": https://httpbin.org/get/foo"}', $response);
        self::assertInstanceOf(Opened::class, $this->circuitBreaker->getState());

        // OPEN - no call to client
        $response = $this->circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        self::assertSame('{"uri": https://httpbin.org/get/foo"}', $response);
        self::assertInstanceOf(Opened::class, $this->circuitBreaker->getState());
        $this->waitFor(2 * self::OPEN_THRESHOLD);

        // SWITCH TO HALF OPEN and retry to call the service (still in failure)
        self::assertSame(
            '{"uri": https://httpbin.org/get/foo"}',
            $this->circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );

        // SWITCH BACK TO OPENED
        self::assertInstanceOf(Opened::class, $this->circuitBreaker->getState());

        $this->waitFor(2 * self::OPEN_THRESHOLD);

        // FINALLY, THE RESPONSE IS AVAILABLE
        self::assertSame(
            '{"hello": "world"}',
            $this->circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
        self::assertInstanceOf(Closed::class, $this->circuitBreaker->getState());
    }

    /**
     * The Circuit Breaker can be isolated, once its done it remains
     * Open and so on only fallback responses will be sent.
     *
     * @throws Exception
     */
    public function testOnceCircuitBreakerIsIsolatedNoTrialsAreDone(): void
    {
        $uri = 'https://httpbin.org/get/foo';
        $this->circuitBreaker->call($uri, $this->createFallbackResponse());
        $this->circuitBreaker->isolate($uri);

        $response = $this->circuitBreaker->call($uri, $this->createFallbackResponse());
        self::assertSame('{"uri": https://httpbin.org/get/foo"}', $response);
        self::assertInstanceOf(Isolated::class, $this->circuitBreaker->getState());

        // Let's do 5 calls!

        for ($i = 0; $i < 5; ++$i) {
            $this->circuitBreaker->call($uri, $this->createFallbackResponse());
            self::assertSame('{"uri": https://httpbin.org/get/foo"}', $response);
            self::assertInstanceOf(Isolated::class, $this->circuitBreaker->getState());
        }

        $this->circuitBreaker->reset($uri);

        self::assertInstanceOf(Closed::class, $this->circuitBreaker->getState());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->circuitBreaker = $this->createCircuitBreaker();
    }

    /**
     * @return MainCircuitBreaker the circuit breaker for testing purposes
     *
     * @throws InvalidSystem
     */
    private function createCircuitBreaker(): MainCircuitBreaker
    {
        $symfonyCache = new SymfonyCache(new Psr16Cache(new ArrayAdapter()));
        $eventDispatcherS = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcherS->method('dispatch')
            ->willReturn($this->createMock(stdClass::class))
        ;

        return new MainCircuitBreaker(
            $this->getSystem(),
            $symfonyCache,
            $eventDispatcherS
        );
    }

    /**
     * @return callable the fallback callable
     */
    private function createFallbackResponse(): callable
    {
        return function ($service) {
            return '{"uri": ' . $service->getUri() . '"}';
        };
    }
}
