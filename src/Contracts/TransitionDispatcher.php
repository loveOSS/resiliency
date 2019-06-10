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
     * @param Service $service the service called
     * @param string $transition the Circuit Breaker transition name
     */
    public function dispatch(CircuitBreaker $circuitBreaker, Service $service, string $transition): void;
}
