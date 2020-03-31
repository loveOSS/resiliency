<?php

namespace Resiliency\Storages;

use Psr\SimpleCache\CacheInterface;
use Resiliency\Contracts\Storage;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;

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
    public function saveTransaction(string $serviceUri, Transaction $transaction): bool
    {
        $key = $this->getKey($serviceUri);

        return $this->symfonyCache->set($key, $transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction(string $serviceUri): Transaction
    {
        $key = $this->getKey($serviceUri);

        if ($this->hasTransaction($serviceUri)) {
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
    public function hasTransaction(string $serviceUri): bool
    {
        $key = $this->getKey($serviceUri);

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
