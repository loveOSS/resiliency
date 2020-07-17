<?php

namespace Resiliency\Clients;

use Resiliency\Contracts\Place;
use Resiliency\Contracts\Service;
use Resiliency\Exceptions\UnavailableService;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Symfony implementation of client.
 * The possibility of extending this client is intended.
 * /!\ The HttpClient of Symfony is experimental.
 */
class SymfonyClient extends ClientHelper
{
    /**
     * @var HttpClientInterface the Symfony HTTP client
     */
    private $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        array $mainOptions = []
    ) {
        $this->httpClient = $httpClient;
        parent::__construct($mainOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function request(Service $service, Place $place): string
    {
        $options = [];
        try {
            $method = $this->defineMethod($service->getParameters());
            $options['timeout'] = $this->convertToSeconds($place->getTimeout());

            $clientParameters = array_merge($service->getParameters(), $options);
            unset($clientParameters['method']);

            return $this->httpClient->request($method, $service->getURI(), $clientParameters)->getContent();
        } catch (TransportExceptionInterface $exception) {
            throw new UnavailableService($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
}
