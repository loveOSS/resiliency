<?php

namespace Tests\PrestaShop\CircuitBreaker\Events;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Events\TransitionEvent;

class TransitionEventTest extends TestCase
{
    public function testCreation()
    {
        $event = new TransitionEvent('foo', 'bar', []);

        $this->assertInstanceOf(TransitionEvent::class, $event);
    }

    /**
     * @depends testCreation
     */
    public function testGetService()
    {
        $event = new TransitionEvent('eventName', 'service', []);

        $this->assertSame('service', $event->getService());
    }

    /**
     * @depends testCreation
     */
    public function testGetEvent()
    {
        $event = new TransitionEvent('eventName', 'service', []);

        $this->assertSame('eventName', $event->getEvent());
    }

    /**
     * @depends testCreation
     */
    public function testGetParameters()
    {
        $parameters = [
            'foo' => 'myFoo',
            'bar' => true,
        ];

        $event = new TransitionEvent('eventName', 'service', $parameters);

        $this->assertSame($parameters, $event->getParameters());
    }
}
