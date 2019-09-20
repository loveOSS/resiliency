<?php

namespace Resiliency\Contracts\Monitoring;

/**
 * If you need to monitor and collect information about the circuit breaker
 * use the monitor to collect the information when events are dispatched.
 */
interface Monitor
{
    /**
     * @param ReportEntry $reportEntry the Report Entry
     */
    public function collect(ReportEntry $reportEntry): void;

    /**
     * @return Report the Monitor Report
     */
    public function getReport(): Report;
}
