<?php

namespace Resiliency\Clients;

use Exception;
use GuzzleHttp\Client as OriginalGuzzleClient;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\Service;
use Resiliency\Exceptions\UnavailableService;

/**
 * Guzzle implementation of client.
 * The possibility of extending this client is intended.
 */
class GuzzleClient extends ClientHelper
{
    /**
     * {@inheritdoc}
     */
    public function request(Service $service, Place $place): string
    {
        $options = [];
        try {
            $client = new OriginalGuzzleClient($this->mainOptions);
            $method = $this->defineMethod($service->getParameters());
            $options['http_errors'] = true;
            $options['connect_timeout'] = $this->convertToSeconds($place->getTimeout());
            $options['timeout'] = $this->convertToSeconds($place->getTimeout());

            $clientParameters = array_merge($service->getParameters(), $options);

            return (string) $client->request($method, $service->getURI(), $clientParameters)->getBody();
        } catch (Exception $exception) {
            throw new UnavailableService($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
}
