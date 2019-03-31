<?php

namespace Tests\Resiliency\Places;

use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Places\OpenPlace;
use Resiliency\States;

class OpenPlaceTest extends PlaceTestCase
{
    /**
     * @dataProvider getFixtures
     *
     * @param mixed $failures
     * @param mixed $timeout
     * @param mixed $threshold
     */
    public function testCreationWith($failures, $timeout, $threshold)
    {
        $closedPlace = new OpenPlace($failures, $timeout, $threshold);

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
    public function testCreationWithInvalidValues($failures, $timeout, $threshold)
    {
        $this->expectException(InvalidPlace::class);

        new OpenPlace($failures, $timeout, $threshold);
    }

    public function testGetExpectedState()
    {
        $closedPlace = new OpenPlace(1, 1, 1);

        $this->assertSame(States::OPEN_STATE, $closedPlace->getState());
    }
}
