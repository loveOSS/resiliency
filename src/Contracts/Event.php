<?php

namespace Resiliency\Contracts;

use Psr\Http\Message\RequestInterface;

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
     * @return Request the Request
     */
    public function getRequest(): Request;
}
