<?php

namespace Tests\PrestaShop\CircuitBreaker\Clients;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;

class GuzzleClientTest extends TestCase
{
    public function testRequestWorksAsExpected()
    {
        $client = new GuzzleClient();

        $this->assertNotNull($client->request('http://google.com', [
            'method' => 'GET',
        ]));
    }

    public function testWrongRequestThrowsAnException()
    {
        $this->expectException(UnavailableService::class);

        $client = new GuzzleClient();
        $client->request('http://not-even-a-valid-domain.xxx', []);
    }
}
