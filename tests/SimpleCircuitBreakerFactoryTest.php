<?php

namespace Tests\Resiliency;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\SimpleCircuitBreakerFactory;

class SimpleCircuitBreakerFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreation(): void
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
    public function testCircuitBreakerCreation(array $settings): void
    {
        $factory = new SimpleCircuitBreakerFactory();
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(CircuitBreaker::class, $circuitBreaker);
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return [
            [
                [
                    'failures' => 2,
                    'timeout' => 0.1,
                    'stripped_timeout' => 0.2,
                    'threshold' => 10,
                ],
            ],
            [
                [
                    'failures' => 2,
                    'timeout' => 0.1,
                    'stripped_timeout' => 0.2,
                    'threshold' => 1.0,
                    'client' => [
                        'proxy' => '192.168.16.1:10',
                    ],
                ],
            ],
        ];
    }
}
