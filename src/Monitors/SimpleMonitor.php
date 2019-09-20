<?php

namespace Resiliency\Monitors;

use Resiliency\Contracts\Monitoring\Monitor;
use Resiliency\Contracts\Monitoring\Report;
use Resiliency\Contracts\Event;

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
