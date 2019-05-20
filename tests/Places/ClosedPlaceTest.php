<?php

namespace Tests\Resiliency\Places;

use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Places\ClosedPlace;
use Resiliency\States;

class ClosedPlaceTest extends PlaceTestCase
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
        $closedPlace = new ClosedPlace($failures, $timeout);

        $this->assertSame($failures, $closedPlace->getFailures());
        $this->assertSame($timeout, $closedPlace->getTimeout());
        $this->assertSame($threshold, $closedPlace->getThreshold());
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

        new ClosedPlace($failures, $timeout);
    }

    public function testGetExpectedState(): void
    {
        $closedPlace = new ClosedPlace(1, 1.0);

        $this->assertSame(States::CLOSED_STATE, $closedPlace->getState());
    }
}
