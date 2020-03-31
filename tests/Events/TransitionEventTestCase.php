<?php

namespace Tests\Resiliency\Events;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Event;
use Resiliency\Contracts\Service;

class TransitionEventTestCase extends TestCase
{
    public function checkEventIsValid(string $className): void
    {
        $event = new $className(
            $this->createMock(CircuitBreaker::class),
            $this->createService('bar', [])
        );

        self::assertInstanceOf(Event::class, $event);
        self::assertInstanceOf(Service::class, $event->getService());
        self::assertInstanceOf(CircuitBreaker::class, $event->getCircuitBreaker());
    }

    protected function createService(string $uri, array $parameters): Service
    {
        $service = $this->createMock(Service::class);
        $service->method('getURI')
            ->willReturn($uri)
        ;

        $service->method('getParameters')
            ->willReturn($parameters)
        ;

        return $service;
    }
}
