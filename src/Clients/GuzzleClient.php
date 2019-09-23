<?php

namespace Resiliency\Clients;

use GuzzleHttp\Client as OriginalGuzzleClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Resiliency\Exceptions\UnavailableService;
use Psr\Http\Message\ResponseInterface;
use Exception;

/**
 * Guzzle implementation of client.
 * The possibility of extending this client is intended.
 */
class GuzzleClient extends ClientHelper implements ClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $options = [];
        try {
            $client = new OriginalGuzzleClient($this->mainOptions);

            $timeout = $request->getHeader('RS_TIMEOUT');
            $options['http_errors'] = true;
            $options['connect_timeout'] = $timeout;
            $options['timeout'] = $timeout;

            $clientParameters = array_merge($options);

            return $client->request($request->getMethod(), $request->getURI(), $clientParameters);
        } catch (Exception $exception) {
            throw new UnavailableService(
                $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
