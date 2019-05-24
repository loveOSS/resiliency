<?php

namespace Tests\Resiliency;

use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use Resiliency\TransitionDispatchers\SymfonyDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\EventDispatcher\Event;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Storages\SymfonyCache;
use Resiliency\MainCircuitBreaker;

/**
 * Validates that the right events are dispatched.
 */
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
            'https://httpbin.org/get/foobar',
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

    public function testCircuitBreakerEventsOnIsolationAndResetActions(): void
    {
        $service = 'https://httpbin.org/get/foobaz';
        $circuitBreaker = $this->createCircuitBreaker();

        $circuitBreaker->call(
            $service,
            function () {
                return '{}';
            }
        );

        $circuitBreaker->isolate($service);

        /**
         * The circuit breaker is now isolated and
         * the related event has been dispatched
         */
        $invocations = $this->spy->getInvocations();
        $this->assertCount(5, $invocations);
        $this->assertSame('resiliency.isolating', $invocations[4]->getParameters()[0]);

        /*
         * And now we reset the circuit breaker!
         * The related event must be dispatched
         */
        $circuitBreaker->reset($service);
        $invocations = $this->spy->getInvocations();
        $this->assertCount(6, $invocations);
        $this->assertSame('resiliency.resetting', $invocations[5]->getParameters()[0]);
    }

    private function createCircuitBreaker(): CircuitBreaker
    {
        $system = $this->getSystem();

        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcher::class);
        $eventDispatcherS->expects($this->spy = $this->any())
            ->method('dispatch')
            ->willReturn($this->createMock(Event::class))
        ;

        return new MainCircuitBreaker(
            $system,
            $this->getTestClient(),
            $symfonyCache,
            new SymfonyDispatcher($eventDispatcherS)
        );
    }
}
