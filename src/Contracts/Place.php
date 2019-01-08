<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * A circuit breaker can be in 3 places:
 * closed, half open or open. Each place have its
 * own properties and behaviors.
 */
interface Place
{
    /**
     * Return the current state of the Circuit Breaker.
     *
     * @return string
     */
    public function getState();

    /**
     * @return int the number of failures
     */
    public function getFailures();

    /**
     * @return int the allowed number of trials
     */
    public function getThreshold();

    /**
     * @return float the allowed timeout
     */
    public function getTimeout();
}
