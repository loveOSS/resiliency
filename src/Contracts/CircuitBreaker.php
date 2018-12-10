<?php

namespace PrestaShop\CircuitBreaker\Contracts;

interface CircuitBreaker
{
    /**
     * @var string The service name.
     */
    public function getService();

    /**
     * @var string The circuit breaker state.
     */
    public function getState();

    /**
     * @var Place[] The Circuit Breaker available places.
     */
    public function getPlaces();

    /**
     * The function that execute the service.
     * @var string $service function to call
     */
    public function call($service);
}
