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
        unset($failures, $threshold);
        $closedPlace = new HalfOpenPlace($timeout);

        $this->assertSame(0, $closedPlace->getFailures());
        $this->assertSame($timeout, $closedPlace->getTimeout());
        $this->assertSame(0.0, $closedPlace->getThreshold());
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

        new HalfOpenPlace($timeout);
    }

    public function testGetExpectedState(): void
    {
        $closedPlace = new HalfOpenPlace(1);

        $this->assertSame(States::HALF_OPEN_STATE, $closedPlace->getState());
    }
}
