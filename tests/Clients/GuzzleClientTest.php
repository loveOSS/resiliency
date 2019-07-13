<?php

namespace Tests\Resiliency\Clients;

use Resiliency\Clients\GuzzleClient;
use Resiliency\Contracts\Place;
use Resiliency\Exceptions\UnavailableService;
use Tests\Resiliency\CircuitBreakerTestCase;

class GuzzleClientTest extends CircuitBreakerTestCase
{
    public function testRequestWorksAsExpected()
    {
        $client = new GuzzleClient();
        $service = $this->getService('https://www.google.com', ['method' => 'GET']);

        $this->assertNotNull($client->request($service, $this->createMock(Place::class)));
    }

    public function testWrongRequestThrowsAnException()
    {
        $this->expectException(UnavailableService::class);

        $client = new GuzzleClient();
        $service = $this->getService('http://not-even-a-valid-domain.xxx');

        $client->request($service, $this->createMock(Place::class));
    }

    public function testTheClientAcceptsHttpMethodOverride()
    {
        $client = new GuzzleClient([
            'method' => 'HEAD',
        ]);

        $service = $this->getService('https://www.google.fr');

        $this->assertEmpty(
            $client->request(
                $service,
                $this->createMock(Place::class)
            )
        );
    }
}
