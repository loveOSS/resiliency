<?php

namespace Resiliency;

use Resiliency\Contracts\Service;

/**
 * Represents a service
 */
final class MainService implements Service
{
    /**
     * @var string the Service URI
     */
    private string $uri;

    /**
     * @var array the Service parameters
     */
    private array $parameters;

    public function __construct(string $uri, array $parameters)
    {
        $this->uri = $uri;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getURI(): string
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
