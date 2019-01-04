<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * A circuit breaker is used to provide
 * an alternative response when a tiers service
 * is unreachable.
 */
interface CircuitBreaker
{
    /**
     * @var string the circuit breaker state
     */
    public function getState();

    /**
     * The function that execute the service.
     *
     * @var string the function to call
     * @var callable $fallback if the service is unavailable, rely on the fallback
     */
    public function call($service, callable $fallback);

    /**
     * @var bool checks if the circuit breaker is open
     */
    public function isOpened();

    /**
     * @var bool checks if the circuit breaker is half open
     */
    public function isHalfOpened();

    /**
     * @var bool checks if the circuit breaker is closed
     */
    public function isClosed();
}
