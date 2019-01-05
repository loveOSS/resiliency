<?php

namespace PrestaShop\CircuitBreaker\Storage;

use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;

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
        self::$transactions[md5($service)] = $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction($service)
    {
        if ($this->hasTransaction($service)) {
            return self::$transactions[md5($service)];
        }

        throw new TransactionNotFound();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction($service)
    {
        return array_key_exists(md5($service), self::$transactions);
    }
}
