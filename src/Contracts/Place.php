<?php

namespace PrestaShop\CircuitBreaker\Contracts;

interface Place
{
    /**
     * Execute the service to evaluate.
     *
     * @var string $service service to call
     */
    public function run($service);

    /**
     * Return the current state of the Circuit Breaker.
     *
     * @return string
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
}
