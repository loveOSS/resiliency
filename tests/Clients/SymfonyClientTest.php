<?php

namespace Tests\Resiliency\Clients;

use Resiliency\Contracts\Place;
use Resiliency\Clients\SymfonyClient;
use Tests\Resiliency\CircuitBreakerTestCase;
use Resiliency\Exceptions\UnavailableService;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyClientTest extends CircuitBreakerTestCase
{
    public function testRequestWorksAsExpected(): void
    {
        $client = new SymfonyClient($this->getClient());
        $service = $this->getService('https://www.google.com', ['method' => 'GET']);

        self::assertNotNull($client->request($service, $this->getPlace()));
    }

    public function testWrongRequestThrowsAnException(): void
    {
        $this->expectException(UnavailableService::class);

        $client = new SymfonyClient($this->getClient());
        $service = $this->getService('http://not-even-a-valid-domain.xxx');

        $client->request($service, $this->getPlace());
    }

    public function testTheClientAcceptsHttpMethodOverride(): void
    {
        $client = new SymfonyClient($this->getClient(), [
            'method' => 'HEAD',
        ]);

        $service = $this->getService('https://www.google.fr');

        self::assertEmpty(
            $client->request(
                $service,
                $this->getPlace()
            )
        );
    }

    private function getClient(): HttpClientInterface
    {
        return HttpClient::create();
    }

    private function getPlace(): Place
    {
        $placeMock = $this->createMock(Place::class);
        $placeMock->method('getTimeout')->willReturn(30.0);

        return $placeMock;
    }
}
