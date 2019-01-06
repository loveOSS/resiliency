<?php

namespace PrestaShop\CircuitBreaker\Contracts;

use DateTime;

/**
 * Once the circuit breaker call a service,
 * a transaction is initialized and stored.
 */
interface Transaction
{
    /**
     * @var string the service name
     */
    public function getService();

    /**
     * @var int the number of failures to call the service
     */
    public function getFailures();

    /**
     * @var string the current state of the Circuit Breaker
     */
    public function getState();

    /**
     * @var DateTime the time when the circuit breaker move
     *               from open to half open state
     */
    public function getThresholdDateTime();

    /**
     * @var bool reset the number of failures to 0
     */
    public function resetFailures();

    /**
     * Reset the threshold happens when service is called successfully.
     *
     * @var int the threshold
     *
     * @return bool
     */
    public function resetThresholdDateTime($threshold);

    /**
     * Everytime the service call fails, increment the number of failures.
     *
     * @return bool
     */
    public function incrementFailures();
}
