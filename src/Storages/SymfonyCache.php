<?php

namespace Resiliency\Storages;

use Resiliency\Contracts\Storage;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Implementation of Storage using the Symfony Cache Component.
 */
final class SymfonyCache implements Storage
{
    /**
     * @var AdapterInterface the Symfony Cache
     */
    private $symfonyCache;

    public function __construct(AdapterInterface $cache)
    {
        $this->symfonyCache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTransaction(string $serviceUri, Transaction $transaction): bool
    {
        $item = $this->getItem($serviceUri);
        $item->set($transaction);

        return $this->symfonyCache->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction(string $serviceUri): Transaction
    {
        if ($this->hasTransaction($serviceUri)) {
            $transaction = $this->getItem($serviceUri)->get();

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
        $item = $this->getItem($serviceUri);

        return $item->isHit();
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
     * @return ItemInterface the Cache Item
     */
    private function getItem(string $service): ItemInterface
    {
        return $this->symfonyCache->getItem(md5($service));
    }
}
