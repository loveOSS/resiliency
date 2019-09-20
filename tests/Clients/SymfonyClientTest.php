<?php

namespace Tests\Resiliency\Clients;

use Resiliency\Contracts\Place;
use Resiliency\Clients\SymfonyClient;
use Tests\Resiliency\CircuitBreakerTestCase;
use Resiliency\Exceptions\UnavailableService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Response\MockResponse;

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

        $service = $this->getService('https://www.google.com');

        self::assertSame(
            '',
            $client->request(
                $service,
                $this->getPlace()
            )
        );
    }

    private function getClient(): HttpClientInterface
    {
        $callback = function ($method, $url, $options) {
            if ($url === 'http://not-even-a-valid-domain.xxx/') {
                return new MockResponse('', ['error' => 'Unavailable']);
            }

            if ($method === 'HEAD') {
                return new MockResponse('');
            }

            return new MockResponse('mocked');
        };

        return new MockHttpClient($callback);
    }

    private function getPlace(): Place
    {
        $placeMock = $this->createMock(Place::class);
        $placeMock->method('getTimeout')->willReturn(2.0);

        return $placeMock;
    }
}
