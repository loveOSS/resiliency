<?php

namespace PrestaShop\CircuitBreaker\Storages;

use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;
use PrestaShop\CircuitBreaker\Transactions\SimpleCollection;

/**
 * Implementation of Storage using the Symfony Cache Component.
 */
final class SymfonyCache implements Storage
{
    /**
     * @var AbstractAdapter the Symfony Cache adapter
     */
    private $symfonyCacheAdapter;

    /**
     * @var string the Symfony Cache namespace
     */
    private $namespace;

    public function __construct(AbstractAdapter $symfonyCacheAdapter, $namespace)
    {
        $this->symfonyCacheAdapter = $symfonyCacheAdapter;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTransaction($service, Transaction $transaction)
    {
        $key = $this->getKey($service);
        $cacheItem = $this->symfonyCacheAdapter->getItem($key);

        $cacheItem->set($transaction);

        return $this->symfonyCacheAdapter->save($cacheItem);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction($service)
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            return $this->symfonyCacheAdapter->getItem($key)->get();
        }

        throw new TransactionNotFound();
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactions()
    {
        $transactions = [];
        foreach ($this->symfonyCacheAdapter->getItems([$this->namespace]) as $item) {
            $transactions[] = $item->get();
        }

        return SimpleCollection::create($transactions);
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction($service)
    {
        $key = $this->getKey($service);

        return $this->symfonyCacheAdapter->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->symfonyCacheAdapter->clear();
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
