<?php

namespace Tests\PrestaShop\CircuitBreaker\System;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\States;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Systems\MainSystem;

class MainSystemTest extends TestCase
{
    public function testCreation()
    {
        $openPlace = new OpenPlace(1, 1, 1);
        $halfOpenPlace = new HalfOpenPlace(1, 1, 1);
        $closedPlace = new ClosedPlace(1, 1, 1);

        $mainSystem = new MainSystem(
            $openPlace,
            $halfOpenPlace,
            $closedPlace
        );

        $this->assertInstanceOf(MainSystem::class, $mainSystem);
    }

    /**
     * @depends testCreation
     */
    public function testGetInitialPlace()
    {
        $mainSystem = $this->createMainSystem();
        $initialPlace = $mainSystem->getInitialPlace();

        $this->assertInstanceOf(Place::class, $initialPlace);
        $this->assertSame(States::CLOSED_STATE, $initialPlace->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetPlaces()
    {
        $mainSystem = $this->createMainSystem();
        $places = $mainSystem->getPlaces();

        $this->assertInternalType('array', $places);
        $this->assertCount(3, $places);

        foreach ($places as $place) {
            $this->assertInstanceOf(Place::class, $place);
        }
    }

    /**
     * Returns an instance of MainSystem for tests.
     *
     * @return MainSystem
     */
    private function createMainSystem()
    {
        $openPlace = new OpenPlace(1, 1, 1);
        $halfOpenPlace = new HalfOpenPlace(1, 1, 1);
        $closedPlace = new ClosedPlace(1, 1, 1);

        return new MainSystem(
            $openPlace,
            $halfOpenPlace,
            $closedPlace
        );
    }
}
