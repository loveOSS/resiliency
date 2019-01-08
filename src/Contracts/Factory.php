<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * Ease the creation of the Circuit Breaker.
 */
interface Factory
{
    /**
     * @param array the settings for the Places
     *
     * @return CircuitBreaker
     */
    public function create(array $settings);
}
