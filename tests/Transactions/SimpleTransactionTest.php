<?php

namespace Tests\Resiliency\Transactions;

use DateTime;
use Resiliency\Contracts\Place;
use Resiliency\Exceptions\InvalidTransaction;
use Resiliency\Transactions\SimpleTransaction;
use Tests\Resiliency\CircuitBreakerTestCase;

class SimpleTransactionTest extends CircuitBreakerTestCase
{
    public function testCreation(): void
    {
        $placeStub = $this->createPlaceStub();

        $simpleTransaction = new SimpleTransaction(
            $this->getService('http://some-uri.domain'),
            0,
            $placeStub->getState(),
            2000
        );

        self::assertInstanceOf(SimpleTransaction::class, $simpleTransaction);
    }

    /**
     * @depends testCreation
     */
    public function testGetService(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();

        $service = $simpleTransaction->getService();
        self::assertSame('http://some-uri.domain', $service->getURI());
    }

    /**
     * @depends testCreation
     */
    public function testGetFailures(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();

        self::assertSame(0, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     */
    public function testGetState(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();

        self::assertSame('FAKE_STATE', $simpleTransaction->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetThresholdDateTime(): void
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
    public function testIncrementFailures(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();

        self::assertSame(1, $simpleTransaction->incrementFailures());
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
            ->willReturn(-1000)
        ;

        SimpleTransaction::createFromPlace($placeStub, $this->getService('http://some-uri.domain'));
    }

    private function createSimpleTransaction(): SimpleTransaction
    {
        $placeStub = $this->createPlaceStub();

        return new SimpleTransaction(
            $this->getService('http://some-uri.domain'),
            0,
            $placeStub->getState(),
            2000
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
            ->willReturn(2000)
        ;

        return $placeStub;
    }
}
