<?php

namespace Tests\Resiliency\Storages;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;
use Resiliency\Storages\SimpleCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class SimpleCacheTest extends TestCase
{
    private ?SimpleCache $simpleCache = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->simpleCache = new SimpleCache(
            new Psr16Cache(new FilesystemAdapter('ps__circuit_breaker', 20)
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $filesystemAdapter = new FilesystemAdapter('ps__circuit_breaker', 20);
        $filesystemAdapter->clear();
    }

    public function testCreation(): void
    {
        $symfonyCache = new SimpleCache(
            new Psr16Cache(new FilesystemAdapter('ps__circuit_breaker'))
        );

        self::assertInstanceOf(SimpleCache::class, $symfonyCache);
    }

    /**
     * @depends testCreation
     */
    public function testSaveTransaction(): void
    {
        $operation = $this->simpleCache->saveTransaction(
            'http://test.com',
            $this->createMock(Transaction::class)
        );

        self::assertTrue($operation);
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     */
    public function testHasTransaction(): void
    {
        $this->simpleCache->saveTransaction('http://test.com', $this->createMock(Transaction::class));

        self::assertTrue($this->simpleCache->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     */
    public function testGetTransaction(): void
    {
        $translationStub = $this->createMock(Transaction::class);
        $this->simpleCache->saveTransaction('http://test.com', $translationStub);

        $transaction = $this->simpleCache->getTransaction('http://test.com');

        self::assertEquals($transaction, $translationStub);
    }

    /**
     * @depends testCreation
     * @depends testGetTransaction
     * @depends testHasTransaction
     */
    public function testGetNotFoundTransactionThrowsAnException(): void
    {
        $this->expectException(TransactionNotFound::class);

        $this->simpleCache->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     */
    public function testClear(): void
    {
        $translationStub = $this->createMock(Transaction::class);
        $this->simpleCache->saveTransaction('http://a.com', $translationStub);
        $this->simpleCache->saveTransaction('http://b.com', $translationStub);

        // We have stored 2 transactions
        self::assertTrue($this->simpleCache->clear());
        $this->expectException(TransactionNotFound::class);

        $this->simpleCache->getTransaction('http://a.com');
    }
}
