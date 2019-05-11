<?php

namespace Resiliency\Contracts;

/**
 * Ease the creation of the Circuit Breaker.
 */
interface Factory
{
    /**
     * @param array $settings the settings for the Places
     *
     * @return CircuitBreaker
     */
    public function create(array $settings): CircuitBreaker;
}
