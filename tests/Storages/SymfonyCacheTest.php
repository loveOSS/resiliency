<?php

namespace Tests\PrestaShop\CircuitBreaker\Storages;

use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFound;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Storages\SymfonyCache;
use Symfony\Component\Cache\Simple\FilesystemCache;
use PHPUnit\Framework\TestCase;

class SymfonyCacheTest extends TestCase
{
    /**
     * @var SymfonyCache the Symfony Cache storage
     */
    private $symfonyCache;

    public function testCreation()
    {
        $namespace = 'ps__circuit_breaker';

        $symfonyCache = new SymfonyCache(
            new FilesystemCache($namespace),
            $namespace
        );

        $this->assertInstanceOf(SymfonyCache::class, $symfonyCache);
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

        $this->assertTrue($operation);
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     */
    public function testHasTransaction()
    {
        $this->symfonyCache->saveTransaction('http://test.com', $this->createMock(Transaction::class));

        $this->assertTrue($this->symfonyCache->hasTransaction('http://test.com'));
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

        $this->assertEquals($transaction, $translationStub);
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
        $this->assertTrue($this->symfonyCache->clear());
        $this->expectException(TransactionNotFound::class);

        $this->symfonyCache->getTransaction('http://a.com');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $namespace = 'ps__circuit_breaker';
        $this->symfonyCache = new SymfonyCache(
            new FilesystemCache($namespace, 20),
            $namespace
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $filesystemAdapter = new FilesystemCache('ps__circuit_breaker', 20);
        $filesystemAdapter->clear();
    }
}
