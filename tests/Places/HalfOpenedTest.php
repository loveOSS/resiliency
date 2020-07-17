<?php

namespace Tests\Resiliency\Places;

use Resiliency\Contracts\Client;
use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Places\HalfOpened;
use Resiliency\States;

class HalfOpenedTest extends PlaceTestCase
{
    /**
     * @dataProvider getFixtures
     *
     * @param mixed $failures
     * @param mixed $timeout
     * @param mixed $threshold
     */
    public function testCreationWith($failures, $timeout, $threshold): void
    {
        unset($failures, $threshold);
        $client = $this->createMock(Client::class);
        $halfOpenPlace = new HalfOpened($client, $timeout);

        self::assertSame(0, $halfOpenPlace->getFailures());
        self::assertSame($timeout, $halfOpenPlace->getTimeout());
        self::assertSame(0, $halfOpenPlace->getThreshold());
    }

    /**
     * @dataProvider getInvalidFixtures
     *
     * @param mixed $failures
     * @param mixed $timeout
     * @param mixed $threshold
     */
    public function testCreationWithInvalidValues($failures, $timeout, $threshold): void
    {
        unset($failures, $threshold);
        $this->expectException(InvalidPlace::class);

        $client = $this->createMock(Client::class);
        new HalfOpened($client, $timeout);
    }

    public function testGetExpectedState(): void
    {
        $client = $this->createMock(Client::class);
        $halfOpenPlace = new HalfOpened($client, 1);

        self::assertSame(States::HALF_OPEN_STATE, $halfOpenPlace->getState());
    }
}
