<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PHPUnit\Framework\TestCase;

class SimpleCircuitBreakerFactoryTest extends TestCase
{
    public function testCreation()
    {
        $factory = new SimpleCircuitBreakerFactory();

        $this->assertInstanceOf(SimpleCircuitBreakerFactory::class, $factory);
    }

    /**
     * @depends testCreation
     */
    public function testCircuitBreakerCreation()
    {
        $factory = new SimpleCircuitBreakerFactory();

        $circuitBreaker = $factory->create(
            [
                'closed' => [2, 0.1, 0],
                'open' => [0, 0, 10],
                'half_open' => [1, 0.2, 0],
            ]
        );

        $this->assertInstanceOf(SimpleCircuitBreaker::class, $circuitBreaker);
    }
}
