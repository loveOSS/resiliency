<?php

namespace Resiliency\Storages;

use Resiliency\Contracts\Storage;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;

/**
 * Very simple implementation of Storage using a simple PHP array.
 */
final class SimpleArray implements Storage
{
    /**
     * @var array the circuit breaker transactions
     */
    public static $transactions = [];

    /**
     * {@inheritdoc}
     */
    public function saveTransaction(string $service, Transaction $transaction): bool
    {
        $key = $this->getKey($service);

        self::$transactions[$key] = $transaction;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction(string $service): Transaction
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            $transaction = self::$transactions[$key];

            if ($transaction instanceof Transaction) {
                return $transaction;
            }
        }

        throw new TransactionNotFound();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction(string $service): bool
    {
        $key = $this->getKey($service);

        return array_key_exists($key, self::$transactions);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        self::$transactions = [];

        return true;
    }

    /**
     * Helper method to properly store the transaction.
     *
     * @param string $service the service URI
     *
     * @return string the transaction unique identifier
     */
    private function getKey(string $service): string
    {
        return md5($service);
    }
}
