<?php

namespace Resiliency\Contracts;

use Resiliency\Exceptions\UnavailableService;

/**
 * In charge of calling the resource and return a response.
 * Must throw UnavailableService exception if not reachable.
 */
interface Client
{
    /**
     * @var string by default, calls are sent using GET method
     */
    const DEFAULT_METHOD = 'GET';

    /**
     * @param Service $service the Service
     * @param Place $place the place
     *
     * @throws UnavailableService
     */
    public function request(Service $service, Place $place): string;
}
