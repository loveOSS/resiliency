<?php

namespace Resiliency\Clients;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Resiliency\Exceptions\UnavailableService;
use Resiliency\Contracts\Service;
use Resiliency\Contracts\Client;
use Resiliency\Contracts\Place;

/**
 * Symfony implementation of client.
 * The possibility of extending this client is intended.
 * /!\ The HttpClient of Symfony is experimental.
 */
class SymfonyClient implements Client
{
    /**
     * @var array the Client main options
     */
    private $mainOptions;

    /**
     * @var HttpClientInterface the Symfony HTTP client
     */
    private $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        array $mainOptions = []
    ) {
        $this->httpClient = $httpClient;
        $this->mainOptions = $mainOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function request(Service $service, Place $place): string
    {
        $options = [];
        try {
            $method = $this->defineMethod($service->getParameters());
            $options['timeout'] = $place->getTimeout();

            $clientParameters = array_merge($service->getParameters(), $options);
            unset($clientParameters['method']);

            return (string) $this->httpClient->request($method, $service->getURI(), $clientParameters)->getContent();
        } catch (TransportExceptionInterface $exception) {
            throw new UnavailableService(
                $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param array $options the list of options
     *
     * @return string the method
     */
    private function defineMethod(array $options): string
    {
        if (isset($this->mainOptions['method'])) {
            return (string) $this->mainOptions['method'];
        }

        if (isset($options['method'])) {
            return (string) $options['method'];
        }

        return self::DEFAULT_METHOD;
    }
}
