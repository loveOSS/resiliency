<?php

namespace Resiliency\Systems;

use Resiliency\States;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\System;
use Resiliency\Places\OpenPlace;
use Resiliency\Places\ClosedPlace;
use Resiliency\Places\HalfOpenPlace;
use Resiliency\Exceptions\InvalidSystem;

/**
 * Implement the system described by the documentation.
 * The main system is built with 3 places:
 * - A Closed place
 * - A Half Open Place
 * - An Open Place
 */
final class MainSystem implements System
{
    /**
     * @param int   $failures        the number of allowed failures
     * @param float $timeout         the timeout in milliseconds
     * @param float $strippedTimeout the timeout in milliseconds when trying again
     * @param float $threshold       the timeout in milliseconds before trying again
     */
    public function __construct(
        int $failures,
        float $timeout,
        float $strippedTimeout,
        float $threshold
    ) {
        $closedPlace = new ClosedPlace($failures, $timeout, 0);
        $halfOpenPlace = new HalfOpenPlace(0, $strippedTimeout, 0);
        $openPlace = new OpenPlace(0, 0, $threshold);

        $this->places = [
            $closedPlace->getState() => $closedPlace,
            $halfOpenPlace->getState() => $halfOpenPlace,
            $openPlace->getState() => $openPlace,
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
     */
    public static function createFromArray(array $settings): self
    {
        if (array_key_exists('failures', $settings)
            && array_key_exists('timeout', $settings)
            && array_key_exists('stripped_timeout', $settings)
            && array_key_exists('threshold', $settings)
        ) {
            return new self(
                $settings['failures'],
                $settings['timeout'],
                $settings['stripped_timeout'],
                $settings['threshold']
            );
        }

        throw InvalidSystem::missingSettings($settings);
    }
}
