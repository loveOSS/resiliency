<?php

namespace Resiliency;

use Resiliency\Contracts\TransitionDispatcher;
use Resiliency\Transactions\SimpleTransaction;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Transaction;
use Resiliency\Contracts\Service;
use Resiliency\Contracts\Storage;
use Resiliency\Contracts\System;
use Resiliency\Contracts\Place;

/**
 * Main implementation of the Circuit Breaker.
 */
final class MainCircuitBreaker implements CircuitBreaker
{
    public function __construct(
        System $system,
        Storage $storage,
        TransitionDispatcher $transitionDispatcher
    ) {
        $this->currentPlace = $system->getInitialPlace();
        $this->currentPlace->setCircuitBreaker($this);
        $this->places = $system->getPlaces();
        $this->storage = $storage;
        $this->transitionDispatcher = $transitionDispatcher;
    }

    /**
     * @var Place the current Place of the Circuit Breaker
     */
    private $currentPlace;

    /**
     * @var Place[] the Circuit Breaker places
     */
    private $places;

    /**
     * @var Storage the Circuit Breaker storage
     */
    private $storage;

    /**
     * @var TransitionDispatcher the Circuit Breaker transition dispatcher
     */
    private $transitionDispatcher;

    /**
     * {@inheritdoc}
     */
    public function call(string $uri, callable $fallback, array $uriParameters = []): string
    {
        $service = new MainService($uri, $uriParameters);
        $transaction = $this->initTransaction($service);

        return $this->currentPlace->call($transaction, $fallback);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): Place
    {
        return $this->currentPlace;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatcher(): TransitionDispatcher
    {
        return $this->transitionDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function isolate(string $uri): CircuitBreaker
    {
        $service = $this->storage
            ->getTransaction($uri)
            ->getService()
        ;

        $this->dispatch(Transitions::ISOLATING_TRANSITION, $service);
        $this->moveStateTo(States::ISOLATED_STATE, $service);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $uri): CircuitBreaker
    {
        $service = $this->storage
            ->getTransaction($uri)
            ->getService()
        ;

        $this->dispatch(Transitions::RESETTING_TRANSITION, $service);
        $this->moveStateTo(States::CLOSED_STATE, $service);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function moveStateTo($state, Service $service): CircuitBreaker
    {
        $this->currentPlace = $this->places[$state];
        $this->currentPlace->setCircuitBreaker($this);
        $transaction = SimpleTransaction::createFromPlace(
            $this->currentPlace,
            $service
        );

        $this->storage->saveTransaction($service->getURI(), $transaction);

        return $this;
    }

    /**
     * @todo: refactor to remove this function in favor of moveStateTo
     *
     * @param Service $service the service
     *
     * @return Transaction
     */
    private function initTransaction(Service $service): Transaction
    {
        if ($this->storage->hasTransaction($service->getURI())) {
            $transaction = $this->storage->getTransaction($service->getURI());
            $this->currentPlace = $this->places[$transaction->getState()];
            $this->currentPlace->setCircuitBreaker($this);
        } else {
            $transaction = SimpleTransaction::createFromPlace(
                $this->currentPlace,
                $service
            );

            $this->dispatch(Transitions::INITIATING_TRANSITION, $service);

            $this->storage->saveTransaction($service->getURI(), $transaction);
        }

        return $transaction;
    }

    /**
     * Helper to dispatch transition events.
     *
     * @param string $transition the transition name
     * @param Service $service the service
     */
    private function dispatch(string $transition, Service $service): void
    {
        $this->transitionDispatcher
            ->dispatch(
                $this,
                $service,
                $transition
            );
    }
}
