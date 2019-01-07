<?php

namespace Tests\PrestaShop\CircuitBreaker\Storages;

use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Storages\SimpleArray;
use PHPUnit\Framework\TestCase;

class SimpleArrayTest extends TestCase
{
    public function testCreation()
    {
        $simpleArray = new SimpleArray();

        $this->assertCount(0, $simpleArray::$transactions);
        $this->assertInstanceOf(SimpleArray::class, $simpleArray);
    }

    /**
     * @depends testCreation
     */
    public function testSaveTransaction()
    {
        $simpleArray = new SimpleArray();
        $operation = $simpleArray->saveTransaction(
            'http://test.com',
            $this->createMock(Transaction::class)
        );
        $this->assertTrue($operation);
        $this->assertCount(1, $simpleArray::$transactions);
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     */
    public function testHasTransaction()
    {
        $simpleArray = new SimpleArray();
        $simpleArray->saveTransaction('http://test.com', $this->createMock(Transaction::class));

        $this->assertTrue($simpleArray->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     */
    public function testGetTransaction()
    {
        $simpleArray = new SimpleArray();
        $translationStub = $this->createMock(Transaction::class);
        $simpleArray->saveTransaction('http://test.com', $translationStub);

        $transaction = $simpleArray->getTransaction('http://test.com');

        $this->assertSame($transaction, $translationStub);
    }

    /**
     * @depends testCreation
     * @depends testGetTransaction
     * @depends testHasTransaction
     */
    public function testGetNotFoundTransactionThrowsAnException()
    {
        $this->expectException(TransactionNotFound::class);

        $simpleArray = new SimpleArray();
        $simpleArray->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     */
    public function testClear()
    {
        $simpleArray = new SimpleArray();
        $translationStub = $this->createMock(Transaction::class);
        $simpleArray->saveTransaction('http://a.com', $translationStub);
        $simpleArray->saveTransaction('http://b.com', $translationStub);

        // We have stored 2 transactions
        $simpleArray->clear();
        $transactions = $simpleArray::$transactions;
        $this->assertEmpty($transactions);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $simpleArray = new SimpleArray();
        $simpleArray::$transactions = [];
    }
}
