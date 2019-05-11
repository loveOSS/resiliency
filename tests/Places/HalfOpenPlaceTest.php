<?php

namespace Tests\Resiliency\Places;

use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Places\HalfOpenPlace;
use Resiliency\States;

class HalfOpenPlaceTest extends PlaceTestCase
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
        $closedPlace = new HalfOpenPlace($failures, $timeout, $threshold);

        $this->assertSame($failures, $closedPlace->getFailures());
        $this->assertSame($timeout, $closedPlace->getTimeout());
        $this->assertSame($threshold, $closedPlace->getThreshold());
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
        $this->expectException(InvalidPlace::class);

        new HalfOpenPlace($failures, $timeout, $threshold);
    }

    public function testGetExpectedState(): void
    {
        $closedPlace = new HalfOpenPlace(1, 1.0, 1.0);

        $this->assertSame(States::HALF_OPEN_STATE, $closedPlace->getState());
    }
}
