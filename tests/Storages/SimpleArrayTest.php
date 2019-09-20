<?php

namespace Tests\Resiliency\Storages;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;
use Resiliency\Storages\SimpleArray;

class SimpleArrayTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreation()
    {
        $simpleArray = new SimpleArray();

        self::assertCount(0, $simpleArray->transactions);
        self::assertInstanceOf(SimpleArray::class, $simpleArray);
    }

    /**
     * @depends testCreation
     *
     * @return void
     */
    public function testSaveTransaction()
    {
        $simpleArray = new SimpleArray();
        $operation = $simpleArray->saveTransaction(
            'http://test.com',
            $this->createMock(Transaction::class)
        );
        self::assertTrue($operation);
        self::assertCount(1, $simpleArray->transactions);
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     *
     * @return void
     */
    public function testHasTransaction()
    {
        $simpleArray = new SimpleArray();
        $simpleArray->saveTransaction('http://test.com', $this->createMock(Transaction::class));

        self::assertTrue($simpleArray->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     *
     * @return void
     */
    public function testGetTransaction()
    {
        $simpleArray = new SimpleArray();
        $translationStub = $this->createMock(Transaction::class);
        $simpleArray->saveTransaction('http://test.com', $translationStub);

        $transaction = $simpleArray->getTransaction('http://test.com');

        self::assertSame($transaction, $translationStub);
    }

    /**
     * @depends testCreation
     * @depends testGetTransaction
     * @depends testHasTransaction
     *
     * @return void
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
     *
     * @return void
     */
    public function testClear()
    {
        $simpleArray = new SimpleArray();
        $translationStub = $this->createMock(Transaction::class);
        $simpleArray->saveTransaction('http://a.com', $translationStub);
        $simpleArray->saveTransaction('http://b.com', $translationStub);

        // We have stored 2 transactions
        $simpleArray->clear();
        $transactions = $simpleArray->transactions;
        self::assertEmpty($transactions);
    }
}
