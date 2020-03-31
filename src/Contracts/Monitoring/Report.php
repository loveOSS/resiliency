<?php

namespace Resiliency\Contracts\Monitoring;

/**
 * The Monitor report
 */
interface Report
{
    /**
     * @var ReportEntry the report entry
     */
    public function add(ReportEntry $reportEntry): self;

    /**
     * @return array the list of report entries
     */
    public function all(): array;
}
