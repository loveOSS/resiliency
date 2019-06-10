<?php

namespace Resiliency\TransitionDispatchers;

use Resiliency\Contracts\Service;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\TransitionDispatcher;

/**
 * Very basic implementation of Storage using error_log function.
 * See @doc https://www.php.net/manual/en/function.error-log.php
 */
final class SimpleDispatcher implements TransitionDispatcher
{
    /**
     * @var string the file destination
     */
    private $destination;

    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(CircuitBreaker $circuitBreaker, Service $service, string $transition): void
    {
        $eventMessage = sprintf(
            '[%s]:"%s"_(%s)_%s',
            $transition,
            $service->getURI(),
            $circuitBreaker->getState()->getState(),
            json_encode($service->getParameters())
        );

        error_log($eventMessage, 3, $this->destination);
    }
}
