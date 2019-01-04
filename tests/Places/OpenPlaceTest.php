<?php

namespace Tests\PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Exceptions\InvalidPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\States;

class OpenPlaceTest extends PlaceTestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testCreation($failures, $timeout, $treshold)
    {
        $closedPlace = new OpenPlace($failures, $timeout, $treshold);

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

        $closedPlace = new OpenPlace($failures, $timeout, $treshold);
    }

    public function testState()
    {
        $closedPlace = new OpenPlace(1, 1, 1);

        $this->assertSame(States::OPEN_STATE, $closedPlace->getState());
    }
}
