<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\System\MainSystem;
use PrestaShop\CircuitBreaker\Storages\SimpleArray;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;
use PrestaShop\CircuitBreaker\Transactions\SimpleTransaction;
use PrestaShop\CircuitBreaker\CircuitBreaker\PartialCircuitBreaker;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker extends PartialCircuitBreaker
{
    public function __construct(
        Place $openPlace,
        Place $halfOpenPlace,
        Place $closedPlace,
        Client $client
    ) {
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);
        
        return parent::__construct($system, $client, new SimpleArray());
    }

    /**
     * {@inheritdoc}
     */
    public function call($service, callable $fallback)
    {
        $transaction = $this->initTransaction($service);

        try {
            if ($this->isOpened()) {
                if ($this->canAccessService($transaction)) {
                    $this->moveStateTo(States::HALF_OPEN_STATE, $service);
                }

                return \call_user_func($fallback);
            }

            $response = $this->request($service);
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
}
