<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\States;
use PHPUnit\Framework\TestCase;

class SimpleCircuitBreakerTest extends TestCase
{
    public function testWorkInProgress()
    {
        $circuitBreaker = new SimpleCircuitBreaker(
            new OpenPlace(0, 0, 10),
            new HalfOpenPlace(0, 0.2, 0),
            new ClosedPlace(2, 0.1, 2)
        );

        $fallback = function () {
            return '{}';
        };
        // use case
        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
        $this->assertNull($circuitBreaker->call('https://github.com/_abc_123_404', $fallback));
        $this->assertSame(States::OPEN_STATE, $circuitBreaker->getState());

        // wait for 2 secondes => State should become Half Open
        sleep(2);
        $this->assertSame('{}', $circuitBreaker->call('https://github.com/', $fallback));

        $this->markTestIncomplete('This feature has not been implemented yet.');
        $this->assertSame(States::HALF_OPEN_STATE, $circuitBreaker->getState());
    }
}
