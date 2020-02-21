<?php

namespace Tests\Resiliency\Transactions;

use DateTime;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\Service;
use Resiliency\Transactions\SimpleTransaction;
use Resiliency\Exceptions\InvalidTransaction;
use Tests\Resiliency\CircuitBreakerTestCase;

class SimpleTransactionTest extends CircuitBreakerTestCase
{
    public function testCreation()
    {
        $placeStub = $this->createPlaceStub();

        $simpleTransaction = new SimpleTransaction(
            $this->getService('http://some-uri.domain'),
            0,
            $placeStub->getState(),
            2
        );

        self::assertInstanceOf(SimpleTransaction::class, $simpleTransaction);
    }

    /**
     * @depends testCreation
     */
    public function testGetService()
    {
        $simpleTransaction = $this->createSimpleTransaction();

        self::assertInstanceOf(Service::class, $simpleTransaction->getService());
        $service = $simpleTransaction->getService();
        self::assertSame('http://some-uri.domain', $service->getURI());
    }

    /**
     * @depends testCreation
     */
    public function testGetFailures()
    {
        $simpleTransaction = $this->createSimpleTransaction();

        self::assertSame(0, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     */
    public function testGetState()
    {
        $simpleTransaction = $this->createSimpleTransaction();

        self::assertSame('FAKE_STATE', $simpleTransaction->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetThresholdDateTime()
    {
        $simpleTransaction = $this->createSimpleTransaction();
        $expectedDateTime = (new DateTime('+2 second'))->format('d/m/Y H:i:s');
        $simpleTransactionDateTime = $simpleTransaction->getThresholdDateTime()->format('d/m/Y H:i:s');

        self::assertSame($expectedDateTime, $simpleTransactionDateTime);
    }

    /**
     * @depends testCreation
     * @depends testGetFailures
     */
    public function testIncrementFailures()
    {
        $simpleTransaction = $this->createSimpleTransaction();
        self::assertSame(1, $simpleTransaction->incrementFailures());

        self::assertSame(1, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     * @depends testGetFailures
     */
    public function testClearFailures()
    {
        $simpleTransaction = $this->createSimpleTransaction();
        self::assertSame(1, $simpleTransaction->incrementFailures());
        self::assertSame(1, $simpleTransaction->getFailures());
        self::assertSame(1, $simpleTransaction->clearFailures());
        self::assertSame(0, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     */
    public function testCreationFromPlaceHelper(): void
    {
        $simpleTransactionFromHelper = SimpleTransaction::createFromPlace(
            $this->createPlaceStub(),
            $this->getService('http://some-uri.domain')
        );

        $simpleTransaction = $this->createSimpleTransaction();

        self::assertSame($simpleTransactionFromHelper->getState(), $simpleTransaction->getState());
        self::assertSame($simpleTransactionFromHelper->getFailures(), $simpleTransaction->getFailures());
        $fromPlaceDate = $simpleTransactionFromHelper->getThresholdDateTime()->format('d/m/Y H:i:s');
        $expectedDate = $simpleTransaction->getThresholdDateTime()->format('d/m/Y H:i:s');

        self::assertSame($fromPlaceDate, $expectedDate);
    }

    /**
     * @depends testCreation
     */
    public function testCreationWithInvalidSettingsWillThrowAnException(): void
    {
        $this->expectException(InvalidTransaction::class);

        $placeStub = $this->createMock(Place::class);

        $placeStub->expects(self::any())
            ->method('getState')
            ->willReturn('FAKE_STATE')
        ;

        $placeStub->expects(self::any())
            ->method('getThreshold')
            ->willReturn(-1.0)
        ;

        SimpleTransaction::createFromPlace($placeStub, $this->getService('http://some-uri.domain'));
    }

    /**
     * Returns an instance of SimpleTransaction for tests.
     *
     * @return SimpleTransaction
     */
    private function createSimpleTransaction()
    {
        $placeStub = $this->createPlaceStub();

        return new SimpleTransaction(
            $this->getService('http://some-uri.domain'),
            0,
            $placeStub->getState(),
            2
        );
    }

    /**
     * Returns an instance of Place with State equals to "FAKE_STATE"
     * and threshold equals to 2.
     *
     * @return Place&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPlaceStub()
    {
        $placeStub = $this->createMock(Place::class);

        $placeStub->expects(self::any())
            ->method('getState')
            ->willReturn('FAKE_STATE')
        ;

        $placeStub->expects(self::any())
            ->method('getThreshold')
            ->willReturn(2.0)
        ;

        return $placeStub;
    }
}
