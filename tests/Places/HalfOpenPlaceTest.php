<?php

namespace Tests\PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Exceptions\InvalidPlace;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\States;

class HalfOpenPlaceTest extends PlaceTestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testCreation($failures, $timeout, $treshold)
    {
        $closedPlace = new HalfOpenPlace($failures, $timeout, $treshold);

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

        $closedPlace = new HalfOpenPlace($failures, $timeout, $treshold);
    }

    public function testState()
    {
        $closedPlace = new HalfOpenPlace(1, 1, 1);

        $this->assertSame(States::HALF_OPEN_STATE, $closedPlace->getState());
    }
}
