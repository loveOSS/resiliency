<?php

namespace Tests\Resiliency\Events;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Service;
use Resiliency\Events\TransitionEvent;

class TransitionEventTest extends TestCase
{
    public function testCreation(): void
    {
        $event = new TransitionEvent(
            $this->createMock(CircuitBreaker::class),
            'foo',
            $this->createService('bar', [])
        );

        $this->assertInstanceOf(TransitionEvent::class, $event);
    }

    /**
     * @depends testCreation
     */
    public function testGetService(): void
    {
        $event = new TransitionEvent(
            $this->createMock(CircuitBreaker::class),
            'foo',
            $this->createService('service', [])
        );

        $this->assertSame('service', $event->getService()->getURI());
    }

    /**
     * @depends testCreation
     */
    public function testGetEvent(): void
    {
        $event = new TransitionEvent(
            $this->createMock(CircuitBreaker::class),
            'eventName',
            $this->createService('bar', [])
        );

        $this->assertSame('eventName', $event->getEvent());
    }

    /**
     * @param string $uri
     * @param array $parameters
     *
     * @return Service
     */
    private function createService(string $uri, array $parameters): Service
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

    /**
     * @depends testCreation
     */
    public function testGetCircuitBreaker(): void
    {
        $event = new TransitionEvent(
            $this->createMock(CircuitBreaker::class),
            'eventName',
            $this->createService('foo', [])
        );

        $this->assertInstanceOf(CircuitBreaker::class, $event->getCircuitBreaker());
    }
}
