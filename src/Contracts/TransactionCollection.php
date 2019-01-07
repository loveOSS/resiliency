<?php

namespace PrestaShop\CircuitBreaker\Contracts;

use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;

/**
 * Helper to manipulate a list of transactions.
 */
interface TransactionCollection extends \Countable
{
    /**
     * @param string $service the Transaction service URI
     *
     * @throws TransactionNotFound
     *
     * @return Transaction
     */
    public function findOneByService($service);

    /**
     * Returns all transactions of the storage.
     *
     * @return array
     */
    public function findAll();

    /**
     * Returns all transactions of the storage
     * using a callable to filter results.
     *
     * @return array
     */
    public function findAllUsing(callable $function);
}
