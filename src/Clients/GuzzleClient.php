<?php

namespace PrestaShop\CircuitBreaker\Clients;

use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;
use PrestaShop\CircuitBreaker\Contracts\Client;
use GuzzleHttp\Client as OriginalGuzzleClient;
use Exception;

/**
 * Guzzle implementation of client.
 * The possibility of extending this client is intended.
 */
class GuzzleClient implements Client
{
    /**
     * {@inheritdoc}
     */
    public function request($resource, array $options)
    {
        try {
            $client = new OriginalGuzzleClient();
            $method = isset($options['method']) ? $options['method'] : 'GET';

            return $client->request($method, $resource, $options)->getBody();
        } catch (Exception $exception) {
            throw new UnavailableService($exception->getMessage());
        }
    }
}
