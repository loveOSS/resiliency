<?php

namespace Resiliency\Monitors;

use Resiliency\Contracts\Monitoring\Report;
use Resiliency\Contracts\Monitoring\ReportEntry;

final class SimpleReport implements Report
{
    private array $reportEntries;

    public function __construct()
    {
        $this->reportEntries = [];
    }

    /**
     * {@inheritdoc}
     */
    public function add(ReportEntry $reportEntry): Report
    {
        $this->reportEntries[] = $reportEntry;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->reportEntries;
    }
}
