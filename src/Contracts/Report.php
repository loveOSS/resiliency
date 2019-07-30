<?php

namespace Resiliency\Contracts;

/**
 * The Monitor report
 */
interface Report
{
    /**
     * @var ReportEntry the report entry
     *
     * @return self
     */
    public function add(ReportEntry $reportEntry): self;

    /**
     * @return array the list of report entries
     * @return array
     */
    public function all(): array;
}
