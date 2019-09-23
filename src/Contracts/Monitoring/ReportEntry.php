<?php

namespace Resiliency\Contracts\Monitoring;

use Resiliency\Contracts\CircuitBreaker;
use Psr\Http\Message\RequestInterface;

/**
 * A report entry store information used to generate a report
 * about Circuit Breaker activity over time.
 */
interface ReportEntry
{
    /**
     * @return RequestInterface the executed request
     */
    public function getRequest(): RequestInterface;

    /**
     * @return CircuitBreaker the circuit breaker used
     */
    public function getCircuitBreaker(): CircuitBreaker;

    /**
     * @return string the current transition of the circuit breaker
     */
    public function getTransition(): string;
}
