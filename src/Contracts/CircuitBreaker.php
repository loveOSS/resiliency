<?php

namespace PrestaShop\CircuitBreaker\Contracts;

interface CircuitBreaker
{
    /**
     * @var string the service name
     */
    public function getService();

    /**
     * @var string the circuit breaker state
     */
    public function getState();

    /**
     * @var Place[] the Circuit Breaker available places
     */
    public function getPlaces();

    /**
     * The function that execute the service.
     *
     * @var string function to call
     * @var callable $fallback if the service is unavailable, rely on the fallback
     */
    public function call($service, callable $fallback);
}
