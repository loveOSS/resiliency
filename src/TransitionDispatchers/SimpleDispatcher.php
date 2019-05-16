<?php

namespace Resiliency\TransitionDispatchers;

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
    public function dispatch(CircuitBreaker $circuitBreaker, $transition, $service, array $parameters): void
    {
        $eventMessage = sprintf(
            '[%s]:"%s"_(%s)_%s',
            $transition,
            $service,
            $circuitBreaker->getState(),
            json_encode($parameters)
        );

        error_log($eventMessage, 3, $this->destination);
    }
}
