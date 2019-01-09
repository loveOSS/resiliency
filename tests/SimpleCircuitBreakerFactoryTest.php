<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;

class SimpleCircuitBreakerFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreation()
    {
        $factory = new SimpleCircuitBreakerFactory();

        $this->assertInstanceOf(SimpleCircuitBreakerFactory::class, $factory);
    }

    /**
     * @depends testCreation
     * @dataProvider getSettings
     *
     * @param array $settings the Circuit Breaker settings
     *
     * @return void
     */
    public function testCircuitBreakerCreation(array $settings)
    {
        $factory = new SimpleCircuitBreakerFactory();
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(SimpleCircuitBreaker::class, $circuitBreaker);
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            [
                [
                    'closed' => [2, 0.1, 0],
                    'open' => [0, 0, 10],
                    'half_open' => [1, 0.2, 0],
                ],
            ],
            [
                [
                    'closed' => [2, 0.1, 0],
                    'open' => [0, 0, 10],
                    'half_open' => [1, 0.2, 0],
                    'client' => ['proxy' => '192.168.16.1:10'],
                ],
            ],
        ];
    }
}
