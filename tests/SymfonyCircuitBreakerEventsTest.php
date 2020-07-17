<?php

namespace Tests\Resiliency;

use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;
use ReflectionException;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Events\Initiated;
use Resiliency\Events\Isolated;
use Resiliency\Events\Opened;
use Resiliency\Events\Reseted;
use Resiliency\Events\Tried;
use Resiliency\MainCircuitBreaker;
use Resiliency\Storages\SymfonyCache;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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
     * The list of invocations of the stubbed event dispatcher
     *
     * @var array
     */
    private $invocations;

    /**
     * We should see the circuit breaker initialized,
     * a call being done and then the circuit breaker closed.
     *
     * @throws ReflectionException
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
        $invocations = self::invocations($this->spy);
        self::assertCount(4, $invocations);

        self::assertInstanceOf(Initiated::class, $invocations[0]->getParameters()[0]);
        self::assertInstanceOf(Tried::class, $invocations[1]->getParameters()[0]);
        self::assertInstanceOf(Tried::class, $invocations[2]->getParameters()[0]);
        self::assertInstanceOf(Opened::class, $invocations[3]->getParameters()[0]);
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
        $invocations = self::invocations($this->spy);
        self::assertCount(5, $invocations);
        self::assertInstanceOf(Isolated::class, $invocations[4]->getParameters()[0]);

        /*
         * And now we reset the circuit breaker!
         * The related event must be dispatched
         */
        $circuitBreaker->reset($service);
        $invocations = self::invocations($this->spy);
        self::assertCount(6, $invocations);
        self::assertInstanceOf(Reseted::class, $invocations[5]->getParameters()[0]);
    }

    private function createCircuitBreaker(): CircuitBreaker
    {
        $system = $this->getSystem();

        $symfonyCache = new SymfonyCache(new ArrayAdapter());
        $eventDispatcherS = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcherS->expects($this->spy = self::any())
            ->method('dispatch')
            ->willReturn($this->createMock(stdClass::class))
        ;

        return new MainCircuitBreaker(
            $system,
            $symfonyCache,
            $eventDispatcherS
        );
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/3888
     *
     * @throws \ReflectionException
     */
    private static function invocations(AnyInvokedCount $anyInvokedCount): array
    {
        $reflectionClass = new ReflectionClass(get_class($anyInvokedCount));
        $parentReflectionClass = $reflectionClass->getParentClass();

        if ($parentReflectionClass instanceof ReflectionClass) {
            foreach ($parentReflectionClass->getProperties() as $property) {
                if ($property->getName() === 'invocations') {
                    $property->setAccessible(true);

                    return $property->getValue($anyInvokedCount);
                }
            }
        }

        return [];
    }
}
