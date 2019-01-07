<?php

namespace PrestaShop\CircuitBreaker\Storages;

use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Contracts\Storage;

/**
 * Very simple implementation of Storage using a simple PHP array.
 */
final class SimpleArray implements Storage
{
    public static $transactions = [];

    /**
     * {@inheritdoc}
     */
    public function saveTransaction($service, Transaction $transaction)
    {
        $key = $this->getKey($service);

        self::$transactions[$key] = $transaction;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction($service)
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            return self::$transactions[$key];
        }

        throw new TransactionNotFound();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction($service)
    {
        $key = $this->getKey($service);

        return array_key_exists($key, self::$transactions);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        self::$transactions = [];
    }

    /**
     * Helper method to properly store the transaction
     *
     * @param string $service the service URI
     *
     * @return string the transaction unique identifier
     */
    private function getKey($service)
    {
        return md5($service);
    }
}
