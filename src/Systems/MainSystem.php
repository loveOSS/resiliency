<?php

namespace Resiliency\Systems;

use Resiliency\Contracts\Place;
use Resiliency\Contracts\System;
use Resiliency\Places\ClosedPlace;
use Resiliency\Places\HalfOpenPlace;
use Resiliency\Places\OpenPlace;
use Resiliency\States;

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
     * @var Place[]
     */
    private $places;

    public function __construct(
        Place $closedPlace,
        Place $halfOpenPlace,
        Place $openPlace
    ) {
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
        $openPlace = OpenPlace::fromArray((array) $settings['open']);
        $halfOpenPlace = HalfOpenPlace::fromArray((array) $settings['half_open']);
        $closedPlace = ClosedPlace::fromArray((array) $settings['closed']);

        return new self($closedPlace, $halfOpenPlace, $openPlace);
    }
}
