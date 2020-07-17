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
     */
    public function getState(): string;

    /**
     * @return int the number of failures
     */
    public function getFailures(): int;

    /**
     * @return int the allowed timeout (in ms) before try to reach the service
     */
    public function getThreshold(): int;

    /**
     * @return int the allowed timeout (in ms)
     */
    public function getTimeout(): int;

    /**
     * The function that execute the service.
     *
     * @param Transaction $transaction the service transaction
     * @param callable $fallback if the service is unavailable, rely on the fallback
     *
     * @throws Exception in case of failure, throws an exception
     */
    public function call(Transaction $transaction, callable $fallback): string;

    /**
     * Set the Circuit Breaker to the place.
     *
     * @param CircuitBreaker $circuitBreaker the circuit breaker
     */
    public function setCircuitBreaker(CircuitBreaker $circuitBreaker): self;
}
