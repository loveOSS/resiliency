<?php

namespace Resiliency;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Factory;
use Resiliency\Places\ClosedPlace;
use Resiliency\Places\HalfOpenPlace;
use Resiliency\Places\OpenPlace;
use Resiliency\Clients\GuzzleClient;

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
        $openPlace = OpenPlace::fromArray((array) $settings['open']);
        $halfOpenPlace = HalfOpenPlace::fromArray((array) $settings['half_open']);
        $closedPlace = ClosedPlace::fromArray((array) $settings['closed']);

        $clientSettings = array_key_exists('client', $settings) ? (array) $settings['client'] : [];
        $client = new GuzzleClient($clientSettings);

        return new SimpleCircuitBreaker(
            $openPlace,
            $halfOpenPlace,
            $closedPlace,
            $client
        );
    }
}
