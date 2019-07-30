<?php

namespace Resiliency\Systems;

use Resiliency\States;
use Resiliency\Contracts\Client;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\System;
use Resiliency\Exceptions\InvalidSystem;
use Resiliency\Places\Opened;
use Resiliency\Places\Closed;
use Resiliency\Places\Isolated;
use Resiliency\Places\HalfOpened;

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
     * @return self
     *
     * @throws InvalidSystem
     */
    public static function createFromArray(array $settings, Client $client): self
    {
        if (self::validate($settings)) {
            return new self(
                $client,
                (int) $settings['failures'],
                (float) $settings['timeout'],
                (float) $settings['stripped_timeout'],
                (float) $settings['threshold']
            );
        }

        throw InvalidSystem::missingSettings($settings);
    }

    /**
     * Ensure the system is valid.
     *
     * @param array $settings the system settings
     *
     * @return bool
     */
    private static function validate(array $settings)
    {
        return array_key_exists('failures', $settings)
            && array_key_exists('timeout', $settings)
            && array_key_exists('stripped_timeout', $settings)
            && array_key_exists('threshold', $settings)
        ;
    }
}
