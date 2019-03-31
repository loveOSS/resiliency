<?php

namespace Resiliency;

use Resiliency\Contracts\Client;
use Resiliency\Contracts\System;
use Resiliency\Contracts\Storage;
use Resiliency\Events\TransitionEvent;
use Resiliency\Exceptions\UnavailableService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Symfony implementation of Circuit Breaker.
 */
final class SymfonyCircuitBreaker extends PartialCircuitBreaker
{
    /**
     * @var EventDispatcherInterface the Symfony Event Dispatcher
     */
    private $eventDispatcher;

    public function __construct(
        System $system,
        Client $client,
        Storage $storage,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct($system, $client, $storage);
    }

    /**
     * {@inheritdoc}
     */
    public function call($service, callable $fallback, $serviceParameters = [])
    {
        $transaction = $this->initTransaction($service);

        try {
            if ($this->isOpened()) {
                if ($this->canAccessService($transaction)) {
                    $this->moveStateTo(States::HALF_OPEN_STATE, $service);
                    $this->dispatch(
                        Transitions::CHECKING_AVAILABILITY_TRANSITION,
                        $service,
                        $serviceParameters
                    );
                }

                return \call_user_func($fallback);
            }

            $response = $this->request($service, $serviceParameters);
            $this->moveStateTo(States::CLOSED_STATE, $service);
            $this->dispatch(
                Transitions::CLOSING_TRANSITION,
                $service,
                $serviceParameters
            );

            return $response;
        } catch (UnavailableService $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);

            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(States::OPEN_STATE, $service);

                $transition = Transitions::OPENING_TRANSITION;

                if ($this->isHalfOpened()) {
                    $transition = Transitions::REOPENING_TRANSITION;
                }

                $this->dispatch($transition, $service, $serviceParameters);

                return \call_user_func($fallback);
            }

            return $this->call($service, $fallback, $serviceParameters);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initTransaction($service)
    {
        if (!$this->storage->hasTransaction($service)) {
            $this->dispatch(Transitions::INITIATING_TRANSITION, $service, []);
        }

        return parent::initTransaction($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function request($service, array $parameters = [])
    {
        $this->dispatch(Transitions::TRIAL_TRANSITION, $service, $parameters);

        return parent::request($service, $parameters);
    }

    /**
     * Helper to dispatch event
     *
     * @param string $eventName the event name
     * @param string $service the URI service called
     * @param array $parameters the service parameters
     *
     * @return object the passed $event object
     */
    private function dispatch($eventName, $service, array $parameters)
    {
        $event = new TransitionEvent($eventName, $service, $parameters);

        return $this->eventDispatcher
            ->dispatch(
                $eventName,
                $event
            )
        ;
    }
}
