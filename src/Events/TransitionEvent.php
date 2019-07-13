<?php

namespace Resiliency\Events;

use Resiliency\Contracts\Service;
use Resiliency\Contracts\CircuitBreaker;
use Symfony\Component\EventDispatcher\Event;

class TransitionEvent extends Event
{
    /**
     * @var CircuitBreaker the Circuit Breaker
     */
    private $circuitBreaker;

    /**
     * @var string the Transition name
     */
    private $eventName;

    /**
     * @var Service the Service URI
     */
    private $service;

    /**
     * @param CircuitBreaker $circuitBreaker the circuit breaker
     * @param string $eventName the transition name
     * @param Service $service the Service
     */
    public function __construct(CircuitBreaker $circuitBreaker, string $eventName, Service $service)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->eventName = $eventName;
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
     * @return string the Transition name
     */
    public function getEvent(): string
    {
        return $this->eventName;
    }

    /**
     * @return Service the Service
     */
    public function getService(): Service
    {
        return $this->service;
    }
}
