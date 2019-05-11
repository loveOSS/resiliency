<?php

namespace Resiliency\Contracts;

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
}
