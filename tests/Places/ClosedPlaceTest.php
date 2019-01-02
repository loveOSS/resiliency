<?php

namespace Tests\PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\States;
use PHPUnit\Framework\TestCase;

class ClosedPlaceTest extends TestCase
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

    public function testState()
    {
        $closedPlace = new ClosedPlace(1, 1, 1);

        $this->assertSame(States::CLOSED_STATE, $closedPlace->getState());
    }

    public function getFixtures()
    {
        return [
            [0, 0, 0],
            [1, 100, 0],
            [-1, null, false], // @todo: should throw an error
        ];
    }
}
