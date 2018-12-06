<?php

namespace PrestaShop\Contracts\CircuitBreaker;

interface Storage
{
    /**
     * Save the CircuitBreaker State.
     * @var string $service the service name.
     * @var string $state the Circuit Breaker state.
     * 
     * @return bool
     */
    public function saveState($service, $state);

    /**
     * Retrieve the CircuitBreaker State.
     * 
     * @var string $service the service name.
     */
    public function getState($service);
}