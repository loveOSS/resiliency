<?php

namespace Tests\Resiliency\Monitors;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Monitoring\Report;
use Resiliency\Contracts\Monitoring\Monitor;
use Resiliency\Contracts\Monitoring\ReportEntry;
use Resiliency\Monitors\SimpleMonitor;

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
        self::expectNotToPerformAssertions();

        $reportEntryMock = $this->createMock(ReportEntry::class);

        $simpleMonitor = new SimpleMonitor();
        $simpleMonitor->collect($reportEntryMock);
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
