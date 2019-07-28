<?php

namespace Resiliency\Contracts;

/**
 * For every transition reached, an event can be dispatched by the system.
 */
interface Event
{
    /**
     * @return CircuitBreaker the Circuit Breaker
     */
    public function getCircuitBreaker(): CircuitBreaker;

    /**
     * @return Service the Service
     */
    public function getService(): Service;
}
