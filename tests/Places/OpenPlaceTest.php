<?php

namespace Tests\PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\States;
use PHPUnit\Framework\TestCase;

class OpenPlaceTest extends TestCase
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

    public function testState()
    {
        $closedPlace = new OpenPlace(1, 1, 1);

        $this->assertSame(States::OPEN_STATE, $closedPlace->getState());
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
