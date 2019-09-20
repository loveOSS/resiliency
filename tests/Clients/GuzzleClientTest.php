<?php

namespace Tests\Resiliency\Clients;

use Resiliency\Clients\GuzzleClient;
use Resiliency\Contracts\Place;
use Resiliency\Exceptions\UnavailableService;
use Tests\Resiliency\CircuitBreakerTestCase;

class GuzzleClientTest extends CircuitBreakerTestCase
{
    public function testRequestWorksAsExpected(): void
    {
        $client = new GuzzleClient();
        $service = $this->getService('https://www.google.com', ['method' => 'GET']);

        self::assertNotNull($client->request($service, $this->createMock(Place::class)));
    }

    public function testWrongRequestThrowsAnException(): void
    {
        $this->expectException(UnavailableService::class);

        $client = new GuzzleClient();
        $service = $this->getService('http://not-even-a-valid-domain.xxx');

        $client->request($service, $this->createMock(Place::class));
    }

    public function testTheClientAcceptsHttpMethodOverride(): void
    {
        $client = new GuzzleClient([
            'method' => 'HEAD',
        ]);

        $service = $this->getService('https://www.google.fr');

        self::assertEmpty(
            $client->request(
                $service,
                $this->createMock(Place::class)
            )
        );
    }
}
