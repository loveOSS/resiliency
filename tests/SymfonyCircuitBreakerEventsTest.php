<?php

namespace Tests\Resiliency;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Resiliency\SymfonyCircuitBreaker;
use Resiliency\Storages\SymfonyCache;
use Resiliency\Places\HalfOpenPlace;
use Resiliency\Systems\MainSystem;
use Resiliency\Places\ClosedPlace;
use Resiliency\Places\OpenPlace;
use Symfony\Component\Cache\Simple\ArrayCache;

class SymfonyCircuitBreakerEventsTest extends CircuitBreakerTestCase
{
    /**
     * Used to track the dispatched events.
     *
     * @var \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount
     */
    private $spy;

    /**
     * We should see the circuit breaker initialized,
     * a call being done and then the circuit breaker closed.
     */
    public function testCircuitBreakerEventsOnFirstFailedCall()
    {
        $circuitBreaker = $this->createCircuitBreaker();

        $circuitBreaker->call(
            'https://httpbin.org/get/foo',
            function () {
                return '{}';
            }
        );

        /**
         * The circuit breaker is initiated
         * the 2 failed trials are done
         * then the conditions are met to open the circuit breaker
         */
        $invocations = $this->spy->getInvocations();
        $this->assertCount(4, $invocations);

        $this->assertSame('INITIATING', $invocations[0]->getParameters()[0]);
        $this->assertSame('TRIAL', $invocations[1]->getParameters()[0]);
        $this->assertSame('TRIAL', $invocations[2]->getParameters()[0]);
        $this->assertSame('OPENING', $invocations[3]->getParameters()[0]);
    }

    /**
     * @return SymfonyCircuitBreaker the circuit breaker for testing purposes
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
}
