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
    public function testCreation($failures, $timeout, $threshold)
    {
        $closedPlace = new OpenPlace($failures, $timeout, $threshold);

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

        $closedPlace = new OpenPlace($failures, $timeout, $threshold);
    }

    public function testState()
    {
        $closedPlace = new OpenPlace(1, 1, 1);

        $this->assertSame(States::OPEN_STATE, $closedPlace->getState());
    }
}
