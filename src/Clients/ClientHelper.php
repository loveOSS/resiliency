<?php

namespace Resiliency\Clients;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ClientHelper implements ClientInterface
{
    /**
     * @var array the Client main options
     */
    protected $mainOptions;

    public function __construct(array $mainOptions = [])
    {
        $this->mainOptions = $mainOptions;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function sendRequest(RequestInterface $request): ResponseInterface;
}
