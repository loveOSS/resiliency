<?php

namespace Tests\Resiliency\System;

use PHPUnit\Framework\TestCase;
use Resiliency\States;
use Resiliency\Contracts\Place;
use Resiliency\Systems\MainSystem;

class MainSystemTest extends TestCase
{
    public function testCreation(): void
    {
        $mainSystem = new MainSystem(1, 1.0, 1.0, 1.0);

        $this->assertInstanceOf(MainSystem::class, $mainSystem);
    }

    /**
     * @depends testCreation
     */
    public function testGetInitialPlace(): void
    {
        $mainSystem = $this->createMainSystem();
        $initialPlace = $mainSystem->getInitialPlace();

        $this->assertSame(States::CLOSED_STATE, $initialPlace->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetPlaces(): void
    {
        $mainSystem = $this->createMainSystem();
        $places = $mainSystem->getPlaces();

        $this->assertIsArray($places);
        $this->assertCount(3, $places);

        foreach ($places as $place) {
            $this->assertInstanceOf(Place::class, $place);
        }
    }

    public function testCreationFromAnArray(): void
    {
        $mainSystem = MainSystem::createFromArray([
            'failures' => 2,
            'timeout' => 0.2,
            'stripped_timeout' => 0.2,
            'threshold' => 1.0,
        ]);

        $this->assertInstanceOf(MainSystem::class, $mainSystem);
    }

    /**
     * Returns an instance of MainSystem for tests.
     *
     * @return MainSystem
     */
    private function createMainSystem(): MainSystem
    {
        return new MainSystem(1, 1.0, 1.0, 1.0);
    }
}
