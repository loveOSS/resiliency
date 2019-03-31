<?php

namespace Resiliency\Clients;

use Exception;
use GuzzleHttp\Client as OriginalGuzzleClient;
use Resiliency\Contracts\Client;
use Resiliency\Exceptions\UnavailableService;

/**
 * Guzzle implementation of client.
 * The possibility of extending this client is intended.
 */
class GuzzleClient implements Client
{
    /**
     * @var string by default, calls are sent using GET method
     */
    const DEFAULT_METHOD = 'GET';

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
            $method = $this->defineMethod($options);
            $options['http_errors'] = true;

            return (string) $client->request($method, $resource, $options)->getBody();
        } catch (Exception $exception) {
            throw new UnavailableService($exception->getMessage());
        }
    }

    /**
     * @param array $options the list of options
     *
     * @return string the method
     */
    private function defineMethod(array $options)
    {
        if (isset($this->mainOptions['method'])) {
            return $this->mainOptions['method'];
        }

        if (isset($options['method'])) {
            return $options['method'];
        }

        return self::DEFAULT_METHOD;
    }
}
