<?php

namespace Resiliency\Contracts;

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
