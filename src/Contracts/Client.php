<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * In charge of calling the resource and return a response.
 * Must throw UnavailableService exception if not reachable.
 */
interface Client
{
    /**
     * @var string The URI of the service to be reached
     * @var array $options the options if needed
     */
    public function request($resource, array $options);
}
