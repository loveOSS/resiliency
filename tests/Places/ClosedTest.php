<?php

namespace Tests\Resiliency\Places;

use Resiliency\Contracts\Client;
use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Places\Closed;
use Resiliency\States;

class ClosedTest extends PlaceTestCase
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
        $client = $this->createMock(Client::class);
        $closedPlace = new Closed($client, $failures, $timeout);

        self::assertSame($failures, $closedPlace->getFailures());
        self::assertSame($timeout, $closedPlace->getTimeout());
        self::assertSame($threshold, $closedPlace->getThreshold());
    }

    /**
     * @dataProvider getInvalidFixtures
     *
     * @param mixed $failures
     * @param mixed $timeout
     */
    public function testCreationWithInvalidValues($failures, $timeout): void
    {
        $this->expectException(InvalidPlace::class);
        $client = $this->createMock(Client::class);

        new Closed($client, $failures, $timeout);
    }

    public function testGetExpectedState(): void
    {
        $client = $this->createMock(Client::class);
        $closedPlace = new Closed($client, 1, 1_000);

        self::assertSame(States::CLOSED_STATE, $closedPlace->getState());
    }
}
