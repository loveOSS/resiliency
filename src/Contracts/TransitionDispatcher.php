<?php

namespace Resiliency\Contracts;

/**
 * A Transition dispatcher is in charge of send information
 * about the Circuit Breaker outside of the system.
 */
interface TransitionDispatcher
{
    /**
     * Dispatch a Circuit Breaker transition.
     *
     * @param CircuitBreaker $circuitBreaker the Circuit Breaker
     * @param string $transition the Circuit Breaker transition name
     * @param string $service the URI service called
     * @param array $parameters the service parameters
     */
    public function dispatch(CircuitBreaker $circuitBreaker, $transition, $service, array $parameters): void;
}
