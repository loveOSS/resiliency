<?php

namespace PrestaShop\CircuitBreaker\Transactions;

use PrestaShop\CircuitBreaker\Contracts\TransactionCollection;
use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;
use PrestaShop\CircuitBreaker\Contracts\Transaction;

final class SimpleCollection implements TransactionCollection
{
    /**
     * @var Transaction[] the list of transactions
     */
    private $transactions;

    public function __construct(array $transactions = [])
    {
        $this->transactions = $transactions;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByService($service)
    {
        foreach ($this->transactions as $transaction) {
            if ($transaction->getService() === $service) {
                return $transaction;
            }
        }

        throw new TransactionNotFound();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->transactions;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllUsing(callable $function)
    {
        return array_filter($this->transactions, $function);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->transactions);
    }

    /**
     * Helper static constructor
     *
     * @param array $transactions
     *
     * @return self
     */
    public static function create(array $transactions)
    {
        return new self($transactions);
    }
}
