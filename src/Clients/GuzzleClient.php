<?php

namespace PrestaShop\CircuitBreaker\Clients;

use Exception;
use GuzzleHttp\Client as OriginalGuzzleClient;
use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;

/**
 * Guzzle implementation of client.
 * The possibility of extending this client is intended.
 */
class GuzzleClient implements Client
{
    /**
     * @var array the Client main options
     */
    private $mainOptions;

    public function __construct(array $mainOptions = [])
    {
        $this->mainOptions = $mainOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function request($resource, array $options)
    {
        try {
            $client = new OriginalGuzzleClient($this->mainOptions);
            $method = isset($options['method']) ? $options['method'] : 'GET';

            return (string) $client->request($method, $resource, $options)->getBody();
        } catch (Exception $exception) {
            throw new UnavailableService($exception->getMessage());
        }
    }
}
