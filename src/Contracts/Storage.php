<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * Store the transaction between the Circuit Breaker
 * and the tiers service.
 */
interface Storage
{
    /**
     * Save the CircuitBreaker transaction.
     *
     * @var string The service name
     * @var Transaction $transaction the transaction
     *
     * @return bool
     */
    public function saveTransaction($service, Transaction $transaction);

    /**
     * Retrieve the CircuitBreaker transaction.
     *
     * @var string the service name
     *
     * @return Transaction
     */
    public function getTransaction($service);
}
