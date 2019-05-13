<?php

namespace Resiliency;

use Resiliency\Contracts\System;
use Resiliency\Contracts\Client;
use Resiliency\Contracts\Storage;
use Resiliency\Exceptions\UnavailableService;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker extends PartialCircuitBreaker
{
    public function __construct(System $system, Client $client, Storage $storage)
    {
        parent::__construct($system, $client, $storage);
    }

    /**
     * {@inheritdoc}
     */
    public function call(string $service, callable $fallback, array $serviceParameters = []): string
    {
        $transaction = $this->initTransaction($service);

        try {
            if ($this->isOpened()) {
                if ($this->canAccessService($transaction)) {
                    $this->moveStateTo(States::HALF_OPEN_STATE, $service);
                }

                return (string) $fallback();
            }

            $response = $this->request($service);
            $this->moveStateTo(States::CLOSED_STATE, $service);

            return $response;
        } catch (UnavailableService $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);

            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(States::OPEN_STATE, $service);

                return (string) $fallback();
            }

            return $this->call($service, $fallback, $serviceParameters);
        }
    }
}
