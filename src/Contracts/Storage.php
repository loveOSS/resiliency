<?php

namespace Resiliency\Contracts;

use Resiliency\Exceptions\TransactionNotFound;

/**
 * Store the transaction between the Circuit Breaker
 * and the tiers service.
 */
interface Storage
{
    /**
     * Save the CircuitBreaker transaction.
     *
     * @param string $serviceUri The service name
     * @param Transaction $transaction The transaction
     *
     * @return bool
     */
    public function saveTransaction(string $serviceUri, Transaction $transaction): bool;

    /**
     * Retrieve the CircuitBreaker transaction for a specific service.
     *
     * @param string $serviceUri the service name
     *
     * @return Transaction
     *
     *@throws TransactionNotFound
     */
    public function getTransaction(string $serviceUri): Transaction;

    /**
     * Checks if the transaction exists.
     *
     * @param string $serviceUri the service name
     *
     * @return bool
     */
    public function hasTransaction(string $serviceUri): bool;

    /**
     * Clear the Circuit Breaker storage.
     *
     * @return bool
     */
    public function clear(): bool;
}
