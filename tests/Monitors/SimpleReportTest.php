<?php

namespace Tests\Resiliency\Monitors;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Monitoring\Report;
use Resiliency\Contracts\Monitoring\ReportEntry;
use Resiliency\Monitors\SimpleReport;

class SimpleReportTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreation()
    {
        self::assertInstanceOf(Report::class, new SimpleReport());
    }

    /**
     * @return void
     */
    public function testAll()
    {
        $simpleReport = new SimpleReport();

        self::assertCount(0, $simpleReport->all());
    }

    /**
     * @return void
     */
    public function testAdd()
    {
        $simpleReport = new SimpleReport();

        $simpleReport->add($this->createMock(ReportEntry::class));

        self::assertCount(1, $simpleReport->all());
    }
}
