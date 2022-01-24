<?php

namespace Resiliency\Events;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Exception;
use Resiliency\Contracts\Service;
use Resiliency\Contracts\ThrowableEvent;

final class Failed extends TransitionEvent implements ThrowableEvent
{
    /**
     * We need to understand why a call have failed.
     *
     * @var Exception the exception
     */
    private $exception;

    /**
     * @param CircuitBreaker $circuitBreaker the circuit breaker
     * @param Service $service the Service
     */
    public function __construct(CircuitBreaker $circuitBreaker, Service $service, Exception $exception)
    {
        $this->exception = $exception;

        parent::__construct($circuitBreaker, $service);
    }

    public function getException(): Exception
    {
        return $this->exception;
    }
}
