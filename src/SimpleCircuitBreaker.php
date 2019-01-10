<?php

namespace PrestaShop\CircuitBreaker;

use DateTime;
use PrestaShop\CircuitBreaker\Contracts\CircuitBreaker;
use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;
use PrestaShop\CircuitBreaker\Storages\SimpleArray;
use PrestaShop\CircuitBreaker\Transactions\SimpleTransaction;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker implements CircuitBreaker
{
    /**
     * @var Client the client in charge of calling the service
     */
    private $client;

    /**
     * @var Place the current Circuit Breaker place
     */
    private $currentPlace;

    /**
     * @var Place[] the Circuit Breaker places
     */
    private $places = [];

    /**
     * @var Storage the Circuit Breaker storage
     */
    private $storage;

    /**
     * Constructor.
     */
    public function __construct(
        Place $openPlace,
        Place $halfOpenPlace,
        Place $closedPlace,
        Client $client
    ) {
        $this->currentPlace = $closedPlace;
        $this->places = [
            States::CLOSED_STATE => $closedPlace,
            States::HALF_OPEN_STATE => $halfOpenPlace,
            States::OPEN_STATE => $openPlace,
        ];

        $this->client = $client;
        $this->storage = new SimpleArray();
    }

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
    public function call($service, callable $fallback)
    {
        $transaction = $this->initTransaction($service);
        // implement the right workflow with a machine state.
        // see schema.

        try {
            if ($this->isOpened()) {
                if ($this->canAccessService($transaction)) {
                    $this->moveStateTo(States::HALF_OPEN_STATE, $service);
                }

                return \call_user_func($fallback);
            }

            $response = $this->tryExecute($service);
            $this->moveStateTo(States::CLOSED_STATE, $service);

            return $response;
        } catch (UnavailableService $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);

            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(States::OPEN_STATE, $service);

                return \call_user_func($fallback);
            }

            return $this->call($service, $fallback);
        }
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
    private function moveStateTo($state, $service)
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
    private function initTransaction($service)
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
    private function isAllowedToRetry(Transaction $transaction)
    {
        return $transaction->getFailures() < $this->currentPlace->getFailures();
    }

    /**
     * @param Transaction $transaction the Transaction
     *
     * @return bool
     */
    private function canAccessService(Transaction $transaction)
    {
        return $transaction->getThresholdDateTime() < new DateTime();
    }

    /**
     * @todo should be moved in its own class maybe?
     *
     * @param string $service the service URI
     *
     * @return string
     */
    private function tryExecute($service)
    {
        return $this->client->request(
            $service,
            [
                'method' => 'GET',
                'http_errors' => true,
                'connect_timeout' => $this->currentPlace->getTimeout(),
                'timeout' => $this->currentPlace->getTimeout(),
            ]
        );
    }
}
