<?php

namespace Tests\Resiliency;

use Resiliency\Clients\GuzzleClient;
use Resiliency\Systems\MainSystem;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;

/**
 * Helper to get a fake Guzzle client.
 */
abstract class CircuitBreakerTestCase extends TestCase
{
    /**
     * Returns an instance of Client able to emulate
     * available and not available services.
     *
     * @return GuzzleClient
     */
    protected function getTestClient(): GuzzleClient
    {
        $mock = new MockHandler([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new Response(200, [], '{"hello": "world"}'),
        ]);

        $handler = HandlerStack::create($mock);

        return new GuzzleClient(['handler' => $handler]);
    }

    /**
     * Returns an instance of Main system shared by all the circuit breakers.
     *
     * @return MainSystem
     */
    protected function getSystem(): MainSystem
    {
        return MainSystem::createFromArray(
            [
                'failures' => 2,
                'timeout' => 0.2,
                'stripped_timeout' => 0.4,
                'threshold' => 1.0,
            ]
        );
    }

    /**
     * Will wait for X seconds, functional wrapper for sleep function.
     *
     * @param int $seconds The number of seconds
     */
    protected function waitFor(int $seconds): void
    {
        sleep($seconds);
    }
}
