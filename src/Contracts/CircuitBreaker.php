<?php

namespace PrestaShop\Contracts\CircuitBreaker;

interface CircuitBreaker
{
    /**
     * @var string the service name.
     */
    public function getService();

    /**
     * @var string the circuit breaker state.
     */
    public function getState();

    /**
     * @var int the number of failures.
     */
    public function getFailures();

    /**
     * @var int the allowed number of trials.
     */
    public function getTreshold();

    /**
     * @var int the allowed timeout.
     */
    public function getTimeout();

    /**
     * The function that execute the service.
     */
    public function run();
}