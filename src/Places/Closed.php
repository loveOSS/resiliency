<?php

namespace Resiliency\Places;

use Resiliency\Contracts\Client;
use Resiliency\Events\Tried;
use Resiliency\Exceptions\UnavailableService;
use Resiliency\Contracts\Transaction;
use Resiliency\States;

/**
 * The circuit initially starts closed. When the circuit is closed:
 *
 * The circuit-breaker executes actions placed through it, measuring the failures and successes of those actions.
 * If the failures exceed a certain threshold, the circuit will break (open).
 */
final class Closed extends PlaceHelper
{
    /**
     * @var Client the client
     */
    private $client;

    /**
     * @param Client $client the Client
     * @param int $failures the Place failures
     * @param float $timeout the Place timeout
     */
    public function __construct(Client $client, int $failures, float $timeout)
    {
        $this->client = $client;
        parent::__construct($failures, $timeout, 0.0);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return States::CLOSED_STATE;
    }

    /**
     * {@inheritdoc}
     */
    public function call(Transaction $transaction, callable $fallback): string
    {
        $service = $transaction->getService();
        $storage = $this->circuitBreaker->getStorage();

        if (!$this->isAllowedToRetry($transaction)) {
            $transaction->clearFailures();
            $this->circuitBreaker->moveStateTo(States::OPEN_STATE, $service);

            return parent::call($transaction, $fallback);
        }

        $this->dispatch(new Tried($this->circuitBreaker, $service));

        try {
            $response = $this->client->request($service, $this);
            $storage->saveTransaction($service->getUri(), $transaction);

            return $response;
        } catch (UnavailableService $exception) {
            $transaction->incrementFailures();
            $storage->saveTransaction($service->getUri(), $transaction);

            return parent::call($transaction, $fallback);
        }
    }
}
