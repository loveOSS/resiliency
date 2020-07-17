<?php

namespace Resiliency\Contracts;

use DateTime;

/**
 * Once the circuit breaker call a service,
 * a transaction is initialized and stored.
 */
interface Transaction
{
    /**
     * @return Service the service
     */
    public function getService(): Service;

    /**
     * @return int the number of failures to call the service
     */
    public function getFailures(): int;

    /**
     * @return string the current state of the Circuit Breaker
     */
    public function getState(): string;

    /**
     * @return DateTime the time when the circuit breaker move
     *                  from open to half open state
     */
    public function getThresholdDateTime(): DateTime;

    /**
     * Every time the service call fails, increment the number of failures.
     */
    public function incrementFailures(): int;

    /**
     * If the service is up again, reset the number of failures to 0.
     */
    public function clearFailures(): bool;
}
