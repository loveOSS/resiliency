<?php

namespace Resiliency\Monitors;

use Resiliency\Contracts\Monitoring\ReportEntry;
use Resiliency\Contracts\Monitoring\Monitor;
use Resiliency\Contracts\Monitoring\Report;

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
