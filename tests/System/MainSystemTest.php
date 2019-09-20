<?php

namespace Tests\Resiliency\System;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Client;
use Resiliency\States;
use Resiliency\Contracts\Place;
use Resiliency\Systems\MainSystem;

class MainSystemTest extends TestCase
{
    public function testCreation(): void
    {
        $mainSystem = new MainSystem(
            $this->createMock(Client::class),
            1,
            1.0,
            1.0,
            1.0
        );

        self::assertInstanceOf(MainSystem::class, $mainSystem);
    }

    /**
     * @depends testCreation
     */
    public function testGetInitialPlace(): void
    {
        $mainSystem = $this->createMainSystem();
        $initialPlace = $mainSystem->getInitialPlace();

        self::assertSame(States::CLOSED_STATE, $initialPlace->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetPlaces(): void
    {
        $mainSystem = $this->createMainSystem();
        $places = $mainSystem->getPlaces();

        self::assertIsArray($places);
        self::assertCount(4, $places);

        foreach ($places as $place) {
            self::assertInstanceOf(Place::class, $place);
        }
    }

    public function testCreationFromAnArray(): void
    {
        $client = $this->createMock(Client::class);

        $mainSystem = MainSystem::createFromArray([
            'failures' => 2,
            'timeout' => 0.2,
            'stripped_timeout' => 0.2,
            'threshold' => 1.0,
        ], $client);

        self::assertCount(4, $mainSystem->getPlaces());
    }

    /**
     * Returns an instance of MainSystem for tests.
     *
     * @return MainSystem
     */
    private function createMainSystem(): MainSystem
    {
        $client = $this->createMock(Client::class);

        return new MainSystem($client, 1, 1.0, 1.0, 1.0);
    }
}
