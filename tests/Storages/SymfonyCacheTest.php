<?php

namespace Tests\PrestaShop\CircuitBreaker\Storages;

use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Storages\SymfonyCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
            new FilesystemAdapter($namespace),
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

        $this->assertCount(1, $this->symfonyCache->getTransactions());
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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $namespace = 'ps__circuit_breaker';
        $this->symfonyCache = new SymfonyCache(
            new FilesystemAdapter($namespace),
            $namespace
        );
    }
}
