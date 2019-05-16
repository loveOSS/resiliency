<?php

namespace Resiliency\TransitionDispatchers;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\TransitionDispatcher;
use Resiliency\Events\TransitionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Implementation of Transition Dispatcher using the Symfony EventDispatcher Component.
 */
final class SymfonyDispatcher implements TransitionDispatcher
{
    /**
     * @var EventDispatcherInterface the Symfony Event Dispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(CircuitBreaker $circuitBreaker, $transition, $service, array $parameters): void
    {
        $event = new TransitionEvent($circuitBreaker, $transition, $service, $parameters);

        $this->eventDispatcher->dispatch(
            'resiliency.' . strtolower($transition),
            $event
        );
    }
}
