<?php

namespace Tests\PrestaShop\CircuitBreaker;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\Systems\MainSystem;
use PrestaShop\CircuitBreaker\SymfonyCircuitBreaker;
use PrestaShop\CircuitBreaker\Storages\SymfonyCache;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\EventDispatcher\EventDispatcher;
use PrestaShop\CircuitBreaker\States;

class SymfonyCircuitBreakerTest extends TestCase
{
    /**
     * Used to track the dispatched events.
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $spy;

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

        /**
         * The circuit breaker is initiated
         * the 2 failed trials are done
         * then the conditions are met to open the circuit breaker
         */
        $invocations = $this->spy->getInvocations();
        $this->assertCount(4, $invocations);
        $this->assertSame('INITIATING', $invocations[0]->parameters[0]);
        $this->assertSame('TRIAL', $invocations[1]->parameters[0]);
        $this->assertSame('TRIAL', $invocations[2]->parameters[0]);
        $this->assertSame('OPENING', $invocations[3]->parameters[0]);
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
     * In HalfOpen state, if the service is back we can
     * close the CircuitBreaker.
     *
     * @depends testCircuitBreakerIsInClosedStateAtStart
     * @depends testCircuitBreakerWillBeOpenedInCaseOfFailures
     * @depends testAfterTheThresholdTheCircuitBreakerMovesInHalfOpenState
     */
    public function testOnceInHalfOpenModeServiceIsFinallyReachable()
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

        sleep(2);
        // CLOSED
        $this->assertSame(
            '{"hello": "world"}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );

        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isClosed());
    }

    /**
     * @return SimpleCircuitBreaker the circuit breaker for testing purposes
     */
    private function createCircuitBreaker()
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );

        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcher::class);
        $eventDispatcherS->expects($this->spy = $this->any())
            ->method('dispatch')
        ;

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

    /**
     * Returns an instance of Client able to emulate
     * available and not available services.
     *
     * @return GuzzleClient
     */
    private function getTestClient()
    {
        $mock = new MockHandler([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new Response(200, [], '{"hello": "world"}'),
        ]);

        $handler = HandlerStack::create($mock);

        return new GuzzleClient(['handler' => $handler]);
    }
}
