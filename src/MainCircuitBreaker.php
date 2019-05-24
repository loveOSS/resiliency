<?php

namespace Resiliency;

use Resiliency\Contracts\TransitionDispatcher;
use Resiliency\Transactions\SimpleTransaction;
use Resiliency\Exceptions\UnavailableService;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Transaction;
use Resiliency\Contracts\Storage;
use Resiliency\Contracts\System;
use Resiliency\Contracts\Client;
use Resiliency\Contracts\Place;
use DateTime;

/**
 * Main implementation of the Circuit Breaker.
 */
final class MainCircuitBreaker implements CircuitBreaker
{
    public function __construct(
        System $system,
        Client $client,
        Storage $storage,
        TransitionDispatcher $transitionDispatcher
    ) {
        $this->currentPlace = $system->getInitialPlace();
        $this->places = $system->getPlaces();
        $this->client = $client;
        $this->storage = $storage;
        $this->transitionDispatcher = $transitionDispatcher;
    }

    /**
     * @var Client the Client that consumes the service URI
     */
    private $client;

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
    public function call(string $service, callable $fallback, array $serviceParameters = []): string
    {
        $transaction = $this->initTransaction($service, $serviceParameters);

        if ($this->isIsolated()) {
            return (string) $fallback();
        }

        if ($this->isOpened()) {
            if (!$this->canAccessService($transaction)) {
                return (string) $fallback();
            }

            $transaction = $this->moveStateTo(States::HALF_OPEN_STATE, $service);
            $this->dispatch(
                Transitions::CHECKING_AVAILABILITY_TRANSITION,
                $service,
                $serviceParameters
            );
        }

        try {
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

                return (string) $fallback();
            }

            return $this->call($service, $fallback, $serviceParameters);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isOpened(): bool
    {
        return States::OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isHalfOpened(): bool
    {
        return States::HALF_OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed(): bool
    {
        return States::CLOSED_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isIsolated(): bool
    {
        return States::ISOLATED_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isolate(string $service): CircuitBreaker
    {
        $this->currentPlace = $this->places[States::ISOLATED_STATE];

        $this->dispatch(Transitions::ISOLATING_TRANSITION, $service, []);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $service): CircuitBreaker
    {
        $this->currentPlace = $this->places[States::CLOSED_STATE];

        $this->dispatch(Transitions::RESETTING_TRANSITION, $service, []);

        return $this;
    }

    /**
     * @param string $state the Place state
     * @param string $service the service URI
     *
     * @return Transaction
     */
    private function moveStateTo($state, $service): Transaction
    {
        $this->currentPlace = $this->places[$state];
        $transaction = SimpleTransaction::createFromPlace(
            $this->currentPlace,
            $service
        );

        $this->storage->saveTransaction($service, $transaction);

        return $transaction;
    }

    /**
     * @param string $service the service URI
     * @param array $serviceParameters the service UI parameters
     *
     * @return Transaction
     */
    private function initTransaction(string $service, $serviceParameters): Transaction
    {
        if ($this->storage->hasTransaction($service)) {
            $transaction = $this->storage->getTransaction($service);
            $this->currentPlace = $this->places[$transaction->getState()];
        } else {
            $this->dispatch(Transitions::INITIATING_TRANSITION, $service, $serviceParameters);

            $transaction = SimpleTransaction::createFromPlace(
                $this->currentPlace,
                $service
            );

            $this->storage->saveTransaction($service, $transaction);
        }

        return $transaction;
    }

    /**
     * @param Transaction $transaction the Transaction
     *
     * @return bool
     */
    private function isAllowedToRetry(Transaction $transaction): bool
    {
        return $transaction->getFailures() < $this->currentPlace->getFailures();
    }

    /**
     * @param Transaction $transaction the Transaction
     *
     * @return bool
     */
    private function canAccessService(Transaction $transaction): bool
    {
        return $transaction->getThresholdDateTime() < new DateTime();
    }

    /**
     * Calls the client with the right information.
     *
     * @param string $service the service URI
     * @param array $parameters the service URI parameters
     *
     * @return string
     */
    private function request(string $service, array $parameters = []): string
    {
        $this->dispatch(Transitions::TRIAL_TRANSITION, $service, $parameters);

        return $this->client->request(
            $service,
            array_merge(
                $parameters,
                [
                    'connect_timeout' => $this->currentPlace->getTimeout(),
                    'timeout' => $this->currentPlace->getTimeout(),
                ]
            )
        );
    }

    /**
     * Helper to dispatch transition events.
     *
     * @param string $transition the transition name
     * @param string $service the URI service called
     * @param array $parameters the service parameters
     */
    private function dispatch($transition, $service, array $parameters): void
    {
        $this->transitionDispatcher
            ->dispatch(
                $this,
                $transition,
                $service,
                $parameters
            );
    }
}
