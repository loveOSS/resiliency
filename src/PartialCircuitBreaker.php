<?php

namespace Resiliency;

use Resiliency\Transactions\SimpleTransaction;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Transaction;
use Resiliency\Contracts\Storage;
use Resiliency\Contracts\System;
use Resiliency\Contracts\Client;
use Resiliency\Contracts\Place;
use DateTime;

abstract class PartialCircuitBreaker implements CircuitBreaker
{
    public function __construct(
        System $system,
        Client $client,
        Storage $storage
    ) {
        $this->currentPlace = $system->getInitialPlace();
        $this->places = $system->getPlaces();
        $this->client = $client;
        $this->storage = $storage;
    }

    /**
     * @var Client the Client that consumes the service URI
     */
    protected $client;

    /**
     * @var Place the current Place of the Circuit Breaker
     */
    protected $currentPlace;

    /**
     * @var Place[] the Circuit Breaker places
     */
    protected $places = [];

    /**
     * @var Storage the Circuit Breaker storage
     */
    protected $storage;

    /**
     * {@inheritdoc}
     */
    abstract public function call(string $service, callable $fallback, array $serviceParameters = []): string;

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
     * @param string $state the Place state
     * @param string $service the service URI
     *
     * @return bool
     */
    protected function moveStateTo($state, $service): bool
    {
        $this->currentPlace = $this->places[$state];
        $transaction = SimpleTransaction::createFromPlace(
            $this->currentPlace,
            $service
        );

        return $this->storage->saveTransaction($service, $transaction);
    }

    /**
     * @param string $service the service URI
     *
     * @return Transaction
     */
    protected function initTransaction(string $service): Transaction
    {
        if ($this->storage->hasTransaction($service)) {
            $transaction = $this->storage->getTransaction($service);
        } else {
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
    protected function isAllowedToRetry(Transaction $transaction): bool
    {
        return $transaction->getFailures() < $this->currentPlace->getFailures();
    }

    /**
     * @param Transaction $transaction the Transaction
     *
     * @return bool
     */
    protected function canAccessService(Transaction $transaction): bool
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
    protected function request(string $service, array $parameters = []): string
    {
        return $this->client->request(
            $service,
            array_merge($parameters, [
                'connect_timeout' => $this->currentPlace->getTimeout(),
                'timeout' => $this->currentPlace->getTimeout(),
            ])
        );
    }
}
