<?php

namespace Resiliency\Storages;

use Psr\SimpleCache\CacheInterface;
use Resiliency\Contracts\Storage;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;

/**
 * Implementation of Storage on PSR-16 using the Symfony Cache Component.
 */
final class SimpleCache implements Storage
{
    private CacheInterface $psr16Cache;

    public function __construct(CacheInterface $psr16Cache)
    {
        $this->psr16Cache = $psr16Cache;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTransaction(string $serviceUri, Transaction $transaction): bool
    {
        $key = $this->getKey($serviceUri);

        return $this->psr16Cache->set($key, $transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction(string $serviceUri): Transaction
    {
        $key = $this->getKey($serviceUri);

        if ($this->hasTransaction($serviceUri)) {
            $transaction = $this->psr16Cache->get($key);

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

        return $this->psr16Cache->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->psr16Cache->clear();
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
