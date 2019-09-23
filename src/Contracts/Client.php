<?php

namespace Resiliency\Contracts;

use Resiliency\Exceptions\UnavailableService;
use Psr\Http\Message\ResponseInterface;

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
     *
     * @return ResponseInterface
     */
    public function request(Service $service, Place $place): ResponseInterface;
}
