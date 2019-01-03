<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * Ease the creation of the Circuit Breaker.
 */
interface CircuitBreakerFactory
{
    /**
     * @var array the settings for the Places
     */
    public function create(array $settings);
}
