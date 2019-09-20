<?php

namespace Tests\Resiliency\Storages;

use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\TransactionNotFound;
use Resiliency\Storages\SymfonyCache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class SymfonyCacheTest extends TestCase
{
    /**
     * @var SymfonyCache the Symfony Cache storage
     */
    private $symfonyCache;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->symfonyCache = new SymfonyCache(
            new FilesystemCache('ps__circuit_breaker', 20)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $filesystemAdapter = new FilesystemCache('ps__circuit_breaker', 20);
        $filesystemAdapter->clear();
    }

    public function testCreation()
    {
        $symfonyCache = new SymfonyCache(
            new FilesystemCache('ps__circuit_breaker')
        );

        self::assertInstanceOf(SymfonyCache::class, $symfonyCache);
    }

    /**
     * @depends testCreation
     */
    public function testSaveTransaction()
    {
        $operation = $this->symfonyCache->saveTransaction(
            'http://test.com',
            $this->createMock(Transaction::class)
        );

        self::assertTrue($operation);
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     */
    public function testHasTransaction()
    {
        $this->symfonyCache->saveTransaction('http://test.com', $this->createMock(Transaction::class));

        self::assertTrue($this->symfonyCache->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     */
    public function testGetTransaction()
    {
        $translationStub = $this->createMock(Transaction::class);
        $this->symfonyCache->saveTransaction('http://test.com', $translationStub);

        $transaction = $this->symfonyCache->getTransaction('http://test.com');

        self::assertEquals($transaction, $translationStub);
    }

    /**
     * @depends testCreation
     * @depends testGetTransaction
     * @depends testHasTransaction
     */
    public function testGetNotFoundTransactionThrowsAnException()
    {
        $this->expectException(TransactionNotFound::class);

        $this->symfonyCache->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     */
    public function testClear()
    {
        $translationStub = $this->createMock(Transaction::class);
        $this->symfonyCache->saveTransaction('http://a.com', $translationStub);
        $this->symfonyCache->saveTransaction('http://b.com', $translationStub);

        // We have stored 2 transactions
        self::assertTrue($this->symfonyCache->clear());
        $this->expectException(TransactionNotFound::class);

        $this->symfonyCache->getTransaction('http://a.com');
    }
}
