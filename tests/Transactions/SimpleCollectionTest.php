<?php

namespace Tests\PrestaShop\CircuitBreaker\Transactions;

use PrestaShop\CircuitBreaker\Transactions\SimpleCollection;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PHPUnit\Framework\TestCase;

class SimpleCollectionTest extends TestCase
{
    public function testCreation()
    {
        $collection = new SimpleCollection();

        $this->assertInstanceOf(SimpleCollection::class, $collection);
    }

    public function testFindOneByService()
    {
        $transactionStub = $this->createTransaction('http://www.prestashop.com');
        $collection = new SimpleCollection([$transactionStub]);

        $transaction = $collection->findOneByService('http://www.prestashop.com');
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame($transaction->getService(), 'http://www.prestashop.com');
    }

    /**
     * @todo: checks if the array contains instances of Transaction
     */
    public function testFindAll()
    {
        $transactionsStubs = [
            $this->createTransaction('http://www.prestashop.com'),
            $this->createTransaction('http://www.devdocs.prestashop.com'),
            $this->createTransaction('http://www.contributors.prestashop.com'),
        ];

        $collection = new SimpleCollection($transactionsStubs);

        $transactions = $collection->findAll();
        $this->assertInternalType('array', $transactions);
        $this->assertCount(3, $transactions);
    }

    public function testFindAllUsing()
    {
        $transactionsStubs = [
            $this->createTransaction('http://www.prestashop.com'),
            $this->createTransaction('http://www.devdocs.prestashop.com'),
            $this->createTransaction('http://www.prestonbot.com'),
        ];

        $collection = new SimpleCollection($transactionsStubs);

        $transactions = $collection->findAllUsing(function (Transaction $transaction) {
            return preg_match('/prestashop.com/', $transaction->getService());
        });

        $this->assertInternalType('array', $transactions);
        $this->assertCount(2, $transactions);
    }

    /**
     * Helper to create Transaction stubs.
     *
     * @param string $service the service URI of the transaction
     */
    private function createTransaction($service)
    {
        $transactionStub = $this->createMock(Transaction::class);

        $transactionStub->expects($this->any())
            ->method('getService')
            ->willReturn($service)
        ;

        return $transactionStub;
    }
}
