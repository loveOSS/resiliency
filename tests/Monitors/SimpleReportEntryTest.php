<?php

namespace Tests\Resiliency\Monitors;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Service;
use Resiliency\Contracts\Monitoring\ReportEntry;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Monitors\SimpleReportEntry;

class SimpleReportEntryTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreation()
    {
        self::assertInstanceOf(
            ReportEntry::class,
            $this->getReportEntry()
        );
    }

    /**
     * @return void
     */
    public function testGetCircuitBreaker()
    {
        self::assertInstanceOf(
            CircuitBreaker::class,
            $this->getReportEntry()->getCircuitBreaker()
        );
    }

    /**
     * @return void
     */
    public function testGetService()
    {
        self::assertInstanceOf(
            Service::class,
            $this->getReportEntry()->getService()
        );
    }

    /**
     * @return void
     */
    public function testGetTransition()
    {
        $transition = $this->getReportEntry()->getTransition();

        self::assertIsString($transition);
    }

    /**
     * @return ReportEntry
     */
    private function getReportEntry()
    {
        return new SimpleReportEntry(
            $this->createMock(Service::class),
            $this->createMock(CircuitBreaker::class),
            'initiating'
        );
    }
}
