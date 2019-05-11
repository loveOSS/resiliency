<?php

namespace Tests\Resiliency;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\EventDispatcher\Event;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use Resiliency\SymfonyCircuitBreaker;
use Resiliency\Storages\SymfonyCache;

class SymfonyCircuitBreakerEventsTest extends CircuitBreakerTestCase
{
    /**
     * Used to track the dispatched events.
     *
     * @var AnyInvokedCount
     */
    private $spy;

    /**
     * We should see the circuit breaker initialized,
     * a call being done and then the circuit breaker closed.
     */
    public function testCircuitBreakerEventsOnFirstFailedCall(): void
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

        $this->assertSame('resiliency.initiating', $invocations[0]->getParameters()[0]);
        $this->assertSame('resiliency.trial', $invocations[1]->getParameters()[0]);
        $this->assertSame('resiliency.trial', $invocations[2]->getParameters()[0]);
        $this->assertSame('resiliency.opening', $invocations[3]->getParameters()[0]);
    }

    private function createCircuitBreaker(): SymfonyCircuitBreaker
    {
        $system = $this->getSystem();

        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcher::class);
        $eventDispatcherS->expects($this->spy = $this->any())
            ->method('dispatch')
            ->willReturn($this->createMock(Event::class))
        ;

        return new SymfonyCircuitBreaker(
            $system,
            $this->getTestClient(),
            $symfonyCache,
            $eventDispatcherS
        );
    }
}
