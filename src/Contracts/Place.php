<?php

namespace Resiliency\Contracts;

/**
 * A circuit breaker can be in 4 places: closed, half open, open or isolated.
 * Each place have its own properties and behaviors.
 */
interface Place
{
    /**
     * Return the state.
     *
     * @return string
     */
    public function getState(): string;

    /**
     * @return int the number of failures
     */
    public function getFailures(): int;

    /**
     * @return float the allowed timeout before try to reach the service
     */
    public function getThreshold(): float;

    /**
     * @return float the allowed timeout
     */
    public function getTimeout(): float;

    /**
     * The function that execute the service.
     *
     * @param Transaction $transaction the service transaction
     * @param callable $fallback if the service is unavailable, rely on the fallback
     *
     * @throws Exception in case of failure, throws an exception
     *
     * @return string
     */
    public function call(Transaction $transaction, callable $fallback): string;

    /**
     * Set the Circuit Breaker to the place.
     *
     * @param CircuitBreaker $circuitBreaker the circuit breaker
     *
     * @return self
     */
    public function setCircuitBreaker(CircuitBreaker $circuitBreaker): self;
}
