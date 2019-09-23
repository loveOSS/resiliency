<?php

namespace Resiliency\Clients;

use Resiliency\Contracts\Client;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\Service;
use Psr\Http\Message\ResponseInterface;

abstract class ClientHelper implements Client
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
     * @param array $options the list of options
     *
     * @return string the method
     */
    protected function defineMethod(array $options): string
    {
        if (isset($this->mainOptions['method'])) {
            return (string) $this->mainOptions['method'];
        }

        if (isset($options['method'])) {
            return (string) $options['method'];
        }

        return self::DEFAULT_METHOD;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function request(Service $service, Place $place): ResponseInterface;
}
