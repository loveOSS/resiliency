<?php

namespace Tests\Resiliency\Clients;

use PHPUnit\Framework\TestCase;
use Resiliency\Clients\GuzzleClient;
use Resiliency\Exceptions\UnavailableService;

class GuzzleClientTest extends TestCase
{
    public function testRequestWorksAsExpected()
    {
        $client = new GuzzleClient();

        $this->assertNotNull($client->request('https://www.google.com', [
            'method' => 'GET',
        ]));
    }

    public function testWrongRequestThrowsAnException()
    {
        $this->expectException(UnavailableService::class);

        $client = new GuzzleClient();
        $client->request('http://not-even-a-valid-domain.xxx', []);
    }

    public function testTheClientAcceptsHttpMethodOverride()
    {
        $client = new GuzzleClient([
            'method' => 'HEAD',
        ]);

        $this->assertEmpty($client->request('https://www.google.fr', []));
    }
}
