<?php

namespace Resiliency\Contracts;

use Psr\Http\Message\RequestInterface;
use DateTime;

/**
 * Once the circuit breaker make a request,
 * a transaction is initialized and stored.
 */
interface Transaction
{
    /**
     * @return RequestInterface the request
     */
    public function getRequest(): RequestInterface;

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
     *
     * @return bool
     */
    public function incrementFailures(): bool;

    /**
     * If the service is up again, reset the number of failures to 0.
     *
     * @return bool
     */
    public function clearFailures(): bool;
}
