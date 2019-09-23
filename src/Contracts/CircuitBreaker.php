<?php

namespace Resiliency\Contracts;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A circuit breaker is used to provide
 * an alternative response when a tiers service
 * is unreachable.
 */
interface CircuitBreaker
{
    /**
     * @return Place the circuit breaker state
     */
    public function getState(): Place;

    /**
     * @return Storage the circuit breaker storage
     */
    public function getStorage(): Storage;

    /**
     * @return EventDispatcherInterface the circuit breaker dispatcher
     */
    public function getDispatcher(): EventDispatcherInterface;

    /**
     * The function that try to reach the uri.
     *
     * @param string $uri the uri to call
     * @param array $uriParameters the uri parameters
     * @param callable $fallback if the service is unavailable, rely on the fallback
     *
     * @throws Exception in case of failure, throws an exception
     *
     * @return ResponseInterface
     */
    public function call(string $uri, callable $fallback, array $uriParameters = []): ResponseInterface;

    /**
     * Manually open (and hold open) the Circuit Breaker
     * This can be used for example to take it offline for maintenance.
     *
     * @param string $uri the service URI to call
     *
     * @return self
     */
    public function isolate(string $uri): self;

    /**
     * Reset the breaker to closed state to start accepting actions again.
     *
     * @param string $uri the service URI to call
     *
     * @return self
     */
    public function reset(string $uri): self;

    /**
     * Update the circuit breaker state
     *
     * @param string $state the Place state
     * @param Service $service the service
     *
     * @return self
     */
    public function moveStateTo($state, Service $service): self;
}
