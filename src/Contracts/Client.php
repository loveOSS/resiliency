<?php

namespace Resiliency\Contracts;

/**
 * In charge of calling the resource and return a response.
 * Must throw UnavailableService exception if not reachable.
 */
interface Client
{
    /**
     * @param string $resource the URI of the service to be reached
     * @param array $options the options if needed
     *
     * @return string
     */
    public function request($resource, array $options);
}
