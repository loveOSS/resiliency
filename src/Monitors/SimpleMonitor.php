<?php

namespace Resiliency\Monitors;

use Resiliency\Contracts\Event;
use Resiliency\Contracts\Monitoring\Monitor;
use Resiliency\Contracts\Monitoring\Report;

final class SimpleMonitor implements Monitor
{
    private Report $report;

    public function __construct()
    {
        $this->report = new SimpleReport();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Event $event): void
    {
        $reportEntry = new SimpleReportEntry(
            $event->getService(),
            $event->getCircuitBreaker(),
            get_class($event)
        );

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
