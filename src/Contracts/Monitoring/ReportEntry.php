<?php

namespace Resiliency\Contracts\Monitoring;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Service;

/**
 * A report entry store information used to generate a report
 * about Circuit Breaker activity over time.
 */
interface ReportEntry
{
    /**
     * @return Service the service called
     */
    public function getService(): Service;

    /**
     * @return CircuitBreaker the circuit breaker used
     */
    public function getCircuitBreaker(): CircuitBreaker;

    /**
     * @return string the current transition of the circuit breaker
     */
    public function getTransition(): string;
}
