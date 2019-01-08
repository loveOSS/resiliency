<?php

namespace PrestaShop\CircuitBreaker\Storages;

use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;
use Symfony\Component\Cache\Simple\AbstractCache;

/**
 * Implementation of Storage using the Symfony Cache Component.
 */
final class SymfonyCache implements Storage
{
    /**
     * @var AbstractCache the Symfony Cache
     */
    private $symfonyCache;

    public function __construct(AbstractCache $symfonyCache)
    {
        $this->symfonyCache = $symfonyCache;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTransaction($service, Transaction $transaction)
    {
        $key = $this->getKey($service);

        return $this->symfonyCache->set($key, $transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction($service)
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            return $this->symfonyCache->get($key);
        }

        throw new TransactionNotFound();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction($service)
    {
        $key = $this->getKey($service);

        return $this->symfonyCache->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
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
    private function getKey($service)
    {
        return md5($service);
    }
}
