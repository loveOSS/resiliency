<?php

namespace Resiliency\Events;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Event;
use Resiliency\Contracts\Service;

abstract class TransitionEvent implements Event
{
    /**
     * @var CircuitBreaker the Circuit Breaker
     */
    private $circuitBreaker;

    /**
     * @var Service the Service URI
     */
    private $service;

    /**
     * @param CircuitBreaker $circuitBreaker the circuit breaker
     * @param Service $service the Service
     */
    public function __construct(CircuitBreaker $circuitBreaker, Service $service)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->service = $service;
    }

    /**
     * @return CircuitBreaker the Circuit Breaker
     */
    public function getCircuitBreaker(): CircuitBreaker
    {
        return $this->circuitBreaker;
    }

    /**
     * @return Service the Service
     */
    public function getService(): Service
    {
        return $this->service;
    }
}
