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
    public array $transactions = [];

    /**
     * {@inheritdoc}
     */
    public function saveTransaction(string $serviceUri, Transaction $transaction): bool
    {
        $key = $this->getKey($serviceUri);

        $this->transactions[$key] = $transaction;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction(string $serviceUri): Transaction
    {
        $key = $this->getKey($serviceUri);

        if ($this->hasTransaction($serviceUri)) {
            $transaction = $this->transactions[$key];

            if ($transaction instanceof Transaction) {
                return $transaction;
            }
        }

        throw new TransactionNotFound();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction(string $serviceUri): bool
    {
        $key = $this->getKey($serviceUri);

        return array_key_exists($key, $this->transactions);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->transactions = [];

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
