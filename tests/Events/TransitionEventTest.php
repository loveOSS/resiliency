<?php

namespace Tests\Resiliency\Events;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Events\TransitionEvent;

class TransitionEventTest extends TestCase
{
    public function testCreation(): void
    {
        $event = new TransitionEvent(
            $this->createMock(CircuitBreaker::class),
            'foo',
            'bar',
            []
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
            'service',
            []
        );

        $this->assertSame('service', $event->getService());
    }

    /**
     * @depends testCreation
     */
    public function testGetEvent(): void
    {
        $event = new TransitionEvent(
            $this->createMock(CircuitBreaker::class),
            'eventName',
            'bar',
            []
        );

        $this->assertSame('eventName', $event->getEvent());
    }

    /**
     * @depends testCreation
     */
    public function testGetParameters(): void
    {
        $parameters = [
            'foo' => 'myFoo',
            'bar' => true,
        ];

        $event = new TransitionEvent(
            $this->createMock(CircuitBreaker::class),
            'foo',
            'bar',
            $parameters
        );

        $this->assertSame($parameters, $event->getParameters());
    }
}
