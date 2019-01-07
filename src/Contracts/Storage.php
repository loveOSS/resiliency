<?php

namespace PrestaShop\CircuitBreaker\Contracts;

use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;

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
     * Retrieve the CircuitBreaker transaction for a specific service.
     *
     * @var string the service name
     *
     * @throws TransactionNotFound
     *
     * @return Transaction
     */
    public function getTransaction($service);

    /**
     * Retrieve all the CircuitBreaker transactions.
     *
     * @return TransactionCollection
     */
    public function getTransactions();

    /**
     * Checks if the transaction exists.
     *
     * @var string the service name
     *
     * @return bool
     */
    public function hasTransaction($service);

    /**
     * Clear the Circuit Breaker storage.
     *
     * @return bool
     */
    public function clear();
}
