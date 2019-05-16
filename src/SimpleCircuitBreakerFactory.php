<?php

namespace Resiliency;

use Resiliency\TransitionDispatchers\SimpleDispatcher;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Storages\SimpleArray;
use Resiliency\Clients\GuzzleClient;
use Resiliency\Systems\MainSystem;
use Resiliency\Contracts\Factory;

/**
 * Main implementation of Circuit Breaker Factory
 * Used to create a basic CircuitBreaker instance.
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

        return new MainCircuitBreaker(
            $mainSystem,
            $client,
            new SimpleArray(),
            new SimpleDispatcher('php://stdout')
        );
    }
}
