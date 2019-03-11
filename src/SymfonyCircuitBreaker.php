<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Contracts\System;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Events\TransitionEvent;
use PrestaShop\CircuitBreaker\Contracts\ConfigurableCall;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Symfony implementation of Circuit Breaker.
 */
final class SymfonyCircuitBreaker extends PartialCircuitBreaker implements ConfigurableCall
{
    /**
     * @var EventDispatcherInterface the Symfony Event Dispatcher
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface the PSR Logger
     */
    private $logger;

    public function __construct(
        System $system,
        Client $client,
        Storage $storage,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;

        parent::__construct($system, $client, $storage);
    }

    /**
     * {@inheritdoc}
     */
    public function call($service, callable $fallback)
    {
        return $this->callWithParameters($service, $fallback);
    }

    /**
     * {@inheritdoc}
     */
    public function callWithParameters(
        $service,
        callable $fallback,
        array $serviceParameters = []
    ) {
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

            $response = $this->request($service);
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

            return $this->callWithParameters(
                $service,
                $fallback,
                $serviceParameters
            );
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
     */
    private function dispatch($eventName, $service, array $parameters)
    {
        $event = new TransitionEvent($eventName, $service, $parameters);

        return $this->eventDispatcher
            ->dispatch(
                Transitions::TRIAL_TRANSITION,
                $event
            )
        ;
    }
}
