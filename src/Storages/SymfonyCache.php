<?php

namespace Resiliency\Storages;

use Resiliency\Contracts\Storage;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;
use Psr\SimpleCache\CacheInterface;

/**
 * Implementation of Storage using the Symfony Cache Component.
 */
final class SymfonyCache implements Storage
{
    /**
     * @var CacheInterface the Symfony Cache
     */
    private $symfonyCache;

    public function __construct(CacheInterface $symfonyCache)
    {
        $this->symfonyCache = $symfonyCache;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTransaction(string $service, Transaction $transaction): bool
    {
        $key = $this->getKey($service);

        return $this->symfonyCache->set($key, $transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction(string $service): Transaction
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            $transaction = $this->symfonyCache->get($key);

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

        return $this->symfonyCache->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->symfonyCache->clear();
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
