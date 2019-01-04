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
    public function testCreation($failures, $timeout, $treshold)
    {
        $closedPlace = new ClosedPlace($failures, $timeout, $treshold);

        $this->assertSame($failures, $closedPlace->getFailures());
        $this->assertSame($timeout, $closedPlace->getTimeout());
        $this->assertSame($treshold, $closedPlace->getTreshold());
    }

    /**
     * @dataProvider getInvalidFixtures
     */
    public function testCreationWithInvalidValues($failures, $timeout, $treshold)
    {
        $this->expectException(InvalidPlace::class);

        $closedPlace = new ClosedPlace($failures, $timeout, $treshold);
    }

    public function testState()
    {
        $closedPlace = new ClosedPlace(1, 1, 1);

        $this->assertSame(States::CLOSED_STATE, $closedPlace->getState());
    }
}
