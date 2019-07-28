<?php

namespace Tests\Resiliency;

use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use Psr\EventDispatcher\EventDispatcherInterface;
use Resiliency\Events\Initiated;
use Resiliency\Events\Isolated;
use Resiliency\Events\Reseted;
use Resiliency\Events\Opened;
use Resiliency\Events\Tried;
use Symfony\Component\Cache\Simple\ArrayCache;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Storages\SymfonyCache;
use Resiliency\MainCircuitBreaker;
use stdClass;

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
        //var_dump($invocations);
        $this->assertCount(4, $invocations);

        $this->assertInstanceOf(Initiated::class, $invocations[0]->getParameters()[0]);
        $this->assertInstanceOf(Tried::class, $invocations[1]->getParameters()[0]);
        $this->assertInstanceOf(Tried::class, $invocations[2]->getParameters()[0]);
        $this->assertInstanceOf(Opened::class, $invocations[3]->getParameters()[0]);
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
        $this->assertInstanceOf(Isolated::class, $invocations[4]->getParameters()[0]);

        /*
         * And now we reset the circuit breaker!
         * The related event must be dispatched
         */
        $circuitBreaker->reset($service);
        $invocations = $this->spy->getInvocations();
        $this->assertCount(6, $invocations);
        $this->assertInstanceOf(Reseted::class, $invocations[5]->getParameters()[0]);
    }

    private function createCircuitBreaker(): CircuitBreaker
    {
        $system = $this->getSystem();

        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcherS->expects($this->spy = $this->any())
            ->method('dispatch')
            ->willReturn($this->createMock(stdClass::class))
        ;

        return new MainCircuitBreaker(
            $system,
            $symfonyCache,
            $eventDispatcherS
        );
    }
}
