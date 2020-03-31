<?php

namespace Resiliency\Systems;

use Resiliency\Contracts\Client;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\System;
use Resiliency\Exceptions\InvalidSystem;
use Resiliency\Places\Closed;
use Resiliency\Places\HalfOpened;
use Resiliency\Places\Isolated;
use Resiliency\Places\Opened;
use Resiliency\States;

/**
 * The main system is built with 4 places:
 * - A Closed place
 * - A Half Opened Place
 * - An Opened Place
 * - An Isolated Place
 */
final class MainSystem implements System
{
    /**
     * @var Place[] the list of System places
     */
    private $places;

    /**
     * @param Client $client the client
     * @param int $failures the number of allowed failures
     * @param float $timeout the timeout in milliseconds
     * @param float $strippedTimeout the timeout in milliseconds when trying again
     * @param float $threshold the timeout in milliseconds before trying again
     */
    public function __construct(
        Client $client,
        int $failures,
        float $timeout,
        float $strippedTimeout,
        float $threshold
    ) {
        $closedPlace = new Closed($client, $failures, $timeout);
        $halfOpenPlace = new HalfOpened($client, $strippedTimeout);
        $openPlace = new Opened($threshold);
        $isolatedPlace = new Isolated();

        $this->places = [
            $closedPlace->getState() => $closedPlace,
            $halfOpenPlace->getState() => $halfOpenPlace,
            $openPlace->getState() => $openPlace,
            $isolatedPlace->getState() => $isolatedPlace,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialPlace(): Place
    {
        return $this->places[States::CLOSED_STATE];
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaces(): array
    {
        return $this->places;
    }

    /**
     * @param array $settings the settings for the Places
     *
     * @throws InvalidSystem
     */
    public static function createFromArray(array $settings, Client $client): self
    {
        if (self::validate($settings)) {
            $timeout = (float) $settings['timeout'];
            if (self::validateTimeout($timeout)) {
                return new self(
                    $client,
                    (int) $settings['failures'],
                    $timeout,
                    (float) $settings['stripped_timeout'],
                    (float) $settings['threshold']
                );
            }

            throw InvalidSystem::phpTimeoutExceeded();
        }

        throw InvalidSystem::missingSettings($settings);
    }

    /**
     * Ensure the system is valid.
     *
     * @param array $settings the system settings
     */
    private static function validate(array $settings): bool
    {
        return isset(
            $settings['failures'],
            $settings['timeout'],
            $settings['stripped_timeout'],
            $settings['threshold']
        );
    }

    /**
     * Ensure the configured timeout is valid.
     *
     * @param float $timeout the system tiemout
     */
    private static function validateTimeout(float $timeout): bool
    {
        $maxExecutionTime = ini_get('max_execution_time');

        return $maxExecutionTime == 0 || $maxExecutionTime >= $timeout;
    }
}
