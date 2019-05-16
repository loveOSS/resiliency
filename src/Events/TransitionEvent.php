<?php

namespace Resiliency\Events;

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
     * @var string the Service URI
     */
    private $service;

    /**
     * @var array the Service parameters
     */
    private $parameters;

    /**
     * @param string $eventName the transition name
     * @param string $service the Service URI
     * @param array $parameters the Service parameters
     */
    public function __construct(CircuitBreaker $circuitBreaker, $eventName, $service, array $parameters)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->eventName = $eventName;
        $this->service = $service;
        $this->parameters = $parameters;
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
     * @return string the Service URI
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return array the Service parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
