<?php

namespace Tests\Resiliency\Monitors;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Event;
use Resiliency\Monitors\SimpleMonitor;
use Resiliency\Contracts\Monitoring\Report;
use Resiliency\Contracts\Monitoring\Monitor;

class SimpleMonitorTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreation()
    {
        self::assertInstanceOf(Monitor::class, new SimpleMonitor());
    }

    /**
     * @return void
     */
    public function testCollect()
    {
        $eventMock = $this->createMock(Event::class);

        $simpleMonitor = new SimpleMonitor();

        self::assertNull($simpleMonitor->collect($eventMock));
    }

    /**
     * @return void
     */
    public function testGetReport()
    {
        $simpleMonitor = new SimpleMonitor();

        self::assertInstanceOf(
            Report::class,
            $simpleMonitor->getReport()
        );
    }
}
