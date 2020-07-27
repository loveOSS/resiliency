<?php

namespace Resiliency\Places;

use Resiliency\Contracts\Client;
use Resiliency\Contracts\Transaction;
use Resiliency\Events\AvailabilityChecked;
use Resiliency\Events\Closed;
use Resiliency\Events\ReOpened;
use Resiliency\Exceptions\UnavailableService;
use Resiliency\States;

/**
 * When the circuit is half-opened:
 *
 * the next action will be treated as a trial, to determine the circuit's health.
 *
 * If this call throws a handled exception, that exception is rethrown,
 * and the circuit transitions immediately back to open, and remains open again for the configured timespan.
 *
 * If the call throws no exception, the circuit transitions back to closed.
 */
final class HalfOpened extends PlaceHelper
{
    private Client $client;

    public function __construct(Client $client, int $timeout)
    {
        $this->client = $client;
        parent::__construct(0, $timeout, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return States::HALF_OPEN_STATE;
    }

    /**
     * {@inheritdoc}
     */
    public function call(Transaction $transaction, callable $fallback): string
    {
        $service = $transaction->getService();
        $this->dispatch(new AvailabilityChecked($this->circuitBreaker, $service));

        try {
            $response = $this->client->request($service, $this);

            $this->dispatch(new Closed($this->circuitBreaker, $service));
            $this->circuitBreaker->moveStateTo(States::CLOSED_STATE, $service);
            $transaction->clearFailures();
            $this->circuitBreaker->getStorage()->saveTransaction($service->getUri(), $transaction);

            return $response;
        } catch (UnavailableService $exception) {
            $transaction->incrementFailures();
            $this->circuitBreaker->getStorage()->saveTransaction($service->getUri(), $transaction);

            if (!$this->isAllowedToRetry($transaction)) {
                $this->dispatch(new ReOpened($this->circuitBreaker, $service));
                $this->circuitBreaker->moveStateTo(States::OPEN_STATE, $service);
            }

            return parent::call($transaction, $fallback);
        }
    }
}
