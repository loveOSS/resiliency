<?php

namespace Resiliency\Monitors;

use Resiliency\Contracts\ReportEntry;
use Resiliency\Contracts\Monitor;
use Resiliency\Contracts\Report;

final class SimpleMonitor implements Monitor
{
    /**
     * @var Report the report
     */
    private $report;

    public function __construct()
    {
        $this->report = new SimpleReport();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ReportEntry $reportEntry): void
    {
        $this->report->add($reportEntry);
    }

    /**
     * {@inheritdoc}
     */
    public function getReport(): Report
    {
        return $this->report;
    }
}
