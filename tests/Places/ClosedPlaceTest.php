<?php

namespace Tests\PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Exceptions\InvalidPlace;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\States;

class ClosedPlaceTest extends PlaceTestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testCreationWith($failures, $timeout, $threshold)
    {
        $closedPlace = new ClosedPlace($failures, $timeout, $threshold);

        $this->assertSame($failures, $closedPlace->getFailures());
        $this->assertSame($timeout, $closedPlace->getTimeout());
        $this->assertSame($threshold, $closedPlace->getThreshold());
    }

    /**
     * @dataProvider getInvalidFixtures
     */
    public function testCreationWithInvalidValues($failures, $timeout, $threshold)
    {
        $this->expectException(InvalidPlace::class);

        $closedPlace = new ClosedPlace($failures, $timeout, $threshold);
    }

    public function testGetExpectedState()
    {
        $closedPlace = new ClosedPlace(1, 1, 1);

        $this->assertSame(States::CLOSED_STATE, $closedPlace->getState());
    }
}
