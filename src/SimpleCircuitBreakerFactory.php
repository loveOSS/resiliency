<?php

namespace Resiliency;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Factory;
use Resiliency\Clients\GuzzleClient;
use Resiliency\Systems\MainSystem;
use Resiliency\Storages\SimpleArray;

/**
 * Main implementation of Circuit Breaker Factory
 * Used to create a SimpleCircuitBreaker instance.
 */
final class SimpleCircuitBreakerFactory implements Factory
{
    /**
     * {@inheritdoc}
     */
    public function create(array $settings): CircuitBreaker
    {
        $mainSystem = MainSystem::createFromArray($settings);

        $clientSettings = array_key_exists('client', $settings) ? (array) $settings['client'] : [];
        $client = new GuzzleClient($clientSettings);

        return new SimpleCircuitBreaker(
            $mainSystem,
            $client,
            new SimpleArray()
        );
    }
}
