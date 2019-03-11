<?php

namespace PrestaShop\CircuitBreaker\System;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Contracts\System;
use PrestaShop\CircuitBreaker\States;

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
            States::CLOSED_STATE => $closedPlace,
            States::HALF_OPEN_STATE => $halfOpenPlace,
            States::OPEN_STATE => $openPlace,
        ];
    }

    public function getInitialPlace()
    {
        return $this->places[States::CLOSED_STATE];
    }

    public function getPlaces()
    {
        return $this->places;
    }
}
