<?php

namespace Resiliency\Contracts;

/**
 * A circuit breaker is used to provide
 * an alternative response when a tiers service
 * is unreachable.
 */
interface CircuitBreaker
{
    /**
     * @return string the circuit breaker state
     */
    public function getState(): string;

    /**
     * The function that execute the service.
     *
     * @param string $service the service to call
     * @param array $serviceParameters the service parameters
     * @param callable $fallback if the service is unavailable, rely on the fallback
     *
     * @return string
     */
    public function call(string $service, callable $fallback, array $serviceParameters = []): string;

    /**
     * @return bool checks if the circuit breaker is open
     */
    public function isOpened(): bool;

    /**
     * @return bool checks if the circuit breaker is half open
     */
    public function isHalfOpened(): bool;

    /**
     * @return bool checks if the circuit breaker is closed
     */
    public function isClosed(): bool;

    /**
     * @return bool checks if the circuit breaker is isolated
     */
    public function isIsolated(): bool;

    /**
     * Manually open (and hold open) the Circuit Breaker
     * This can be used for example to take it offline for maintenance.
     *
     * @param string $service the service to call
     *
     * @return self
     */
    public function isolate(string $service): self;

    /**
     * Reset the breaker to closed state to start accepting actions again.
     *
     * @param string $service the service to call
     *
     * @return self
     */
    public function reset(string $service): self;
}
