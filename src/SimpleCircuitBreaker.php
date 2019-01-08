<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Transactions\SimpleTransaction;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;
use PrestaShop\CircuitBreaker\Contracts\CircuitBreaker;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;
use PrestaShop\CircuitBreaker\Storages\SimpleArray;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\Place;
use DateTime;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker implements CircuitBreaker
{
    /**
     * @var Place the current Circuit Breaker place
     */
    private $currentPlace;

    /**
     * @var Place[] the Circuit Breaker places
     */
    private $places = [];

    /**
     * @var Transaction the Circuit Breaker transaction
     */
    private $transaction;

    /**
     * @var Storage the Circuit Breaker storage
     */
    private $storage;

    /**
     * Constructor
     */
    public function __construct(
        Place $openPlace,
        Place $halfOpenPlace,
        Place $closedPlace
        ) {
        $this->currentPlace = $closedPlace;
        $this->places = [
            States::CLOSED_STATE => $closedPlace,
            States::HALF_OPEN_STATE => $halfOpenPlace,
            States::OPEN_STATE => $openPlace,
        ];

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
        $this->initTransaction($service);
        // implement the right workflow with a machine state.
        // see schema.

        try {
            if ($this->isOpened()) {
                if ($this->canAccessService()) {
                    $this->moveStateTo(States::HALF_OPEN_STATE, $service);
                }

                return call_user_func($fallback);
            }

            $response = $this->tryExecute($service);
            $this->moveStateTo(States::CLOSED_STATE, $service);

            return $response;
        } catch (UnavailableService $exception) {
            $this->transaction->incrementFailures();
            $this->storage->saveTransaction($service, $this->transaction);

            if (!$this->isAllowedToRetry()) {
                $this->moveStateTo(States::OPEN_STATE, $service);
            }

            $this->call($service, $fallback);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isOpened()
    {
        return $this->currentPlace->getState() === States::OPEN_STATE;
    }

    /**
     * {@inheritdoc}
     */
    public function isHalfOpened()
    {
        return $this->currentPlace->getState() === States::HALF_OPEN_STATE;
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return $this->currentPlace->getState() === States::CLOSED_STATE;
    }

    private function moveStateTo($state, $service)
    {
        $this->currentPlace = $this->places[$state];
        $this->transaction = SimpleTransaction::createFromPlace(
            $this->currentPlace,
            $service
        );

        $this->storage->saveTransaction($service, $this->transaction);
    }

    private function initTransaction($service)
    {
        if ($this->storage->hasTransaction($service)) {
            $this->transaction = $this->storage->getTransaction($service);
        } else {
            $this->transaction = SimpleTransaction::createFromPlace(
                $this->currentPlace,
                $service
            );

            $this->storage->saveTransaction($service, $this->transaction);
        }
    }

    private function isAllowedToRetry()
    {
        return $this->transaction->getFailures() < $this->currentPlace->getFailures();
    }

    private function canAccessService()
    {
        return $this->transaction->getThresholdDateTime() < new DateTime();
    }

    /**
     * @todo should be moved in its own class maybe?
     */
    private function tryExecute($service)
    {
        $client = new GuzzleClient();

        return $client->request(
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
