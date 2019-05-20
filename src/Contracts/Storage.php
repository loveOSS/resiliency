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
     * @param string      $service     The service name
     * @param Transaction $transaction The transaction
     *
     * @return bool
     */
    public function saveTransaction(string $service, Transaction $transaction): bool;

    /**
     * Retrieve the CircuitBreaker transaction for a specific service.
     *
     * @param string $service the service name
     *
     * @throws TransactionNotFound
     *
     * @return Transaction
     */
    public function getTransaction(string $service): Transaction;

    /**
     * Checks if the transaction exists.
     *
     * @param string $service the service name
     *
     * @return bool
     */
    public function hasTransaction(string $service): bool;

    /**
     * Clear the Circuit Breaker storage.
     *
     * @return bool
     */
    public function clear(): bool;
}
