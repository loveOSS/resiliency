<?php

namespace PrestaShop\CircuitBreaker\System;

use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
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
        ClosedPlace $closedPlace,
        HalfOpenPlace $halfOpenPlace,
        OpenPlace $openPlace
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
