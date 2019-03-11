<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Transactions\SimpleTransaction;
use PrestaShop\CircuitBreaker\Contracts\CircuitBreaker;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\System;
use PrestaShop\CircuitBreaker\Contracts\Client;
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
    abstract public function call($service, callable $fallback);

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isOpened()
    {
        return States::OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isHalfOpened()
    {
        return States::HALF_OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return States::CLOSED_STATE === $this->currentPlace->getState();
    }

    /**
     * @param string $state the Place state
     * @param string $service the service URI
     *
     * @return bool
     */
    protected function moveStateTo($state, $service)
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
    protected function initTransaction($service)
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
    protected function isAllowedToRetry(Transaction $transaction)
    {
        return $transaction->getFailures() < $this->currentPlace->getFailures();
    }

    /**
     * @param Transaction $transaction the Transaction
     *
     * @return bool
     */
    protected function canAccessService(Transaction $transaction)
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
    protected function request($service, array $parameters = [])
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
