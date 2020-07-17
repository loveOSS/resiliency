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
     * @param int $timeout the timeout in milliseconds
     * @param int $strippedTimeout the timeout in milliseconds when trying again
     * @param int $threshold the timeout in milliseconds before trying again
     */
    public function __construct(
        Client $client,
        int $failures,
        int $timeout,
        int $strippedTimeout,
        int $threshold
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
            $timeout = (int) $settings['timeout'];
            if (self::validateTimeout($timeout)) {
                return new self(
                    $client,
                    (int) $settings['failures'],
                    $timeout,
                    (int) $settings['stripped_timeout'],
                    (int) $settings['threshold']
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
     * @param int $timeout the system timeout
     */
    private static function validateTimeout(int $timeout): bool
    {
        $maxExecutionTime = ini_get('max_execution_time');

        return (0 === (int) $maxExecutionTime) || ($maxExecutionTime >= $timeout);
    }
}
