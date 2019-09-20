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
     * @covers \SimpleMonitor::collect
     *
     * @return void
     */
    public function testCollect()
    {
        self::expectNotToPerformAssertions();
        $eventMock = $this->createMock(Event::class);

        $simpleMonitor = new SimpleMonitor();

        $simpleMonitor->collect($eventMock);
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
