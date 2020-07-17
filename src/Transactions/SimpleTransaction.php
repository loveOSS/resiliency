<?php

namespace Resiliency\Transactions;

use DateTime;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\Service;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\InvalidTransaction;
use Resiliency\Utils\Assert;

/**
 * Main implementation of Circuit Breaker transaction.
 */
final class SimpleTransaction implements Transaction
{
    /**
     * @var Service the service
     */
    private $service;

    /**
     * @var int the failures when we call the service
     */
    private $failures;

    /**
     * @var string the Circuit Breaker state
     */
    private $state;

    /**
     * @var DateTime the Transaction threshold datetime
     */
    private $thresholdDateTime;

    /**
     * @param Service $service the service
     * @param int $failures the allowed failures
     * @param string $state the circuit breaker state/place
     * @param int $threshold the place threshold
     *
     * @throws InvalidTransaction
     */
    public function __construct(Service $service, int $failures, string $state, int $threshold)
    {
        $this->validate($service, $failures, $state, $threshold);

        $this->service = $service;
        $this->failures = $failures;
        $this->state = $state;
        $this->initThresholdDateTime($threshold);
    }

    /**
     * {@inheritdoc}
     */
    public function getService(): Service
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailures(): int
    {
        return $this->failures;
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getThresholdDateTime(): DateTime
    {
        return $this->thresholdDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function incrementFailures(): int
    {
        ++$this->failures;

        return $this->failures;
    }

    /**
     * {@inheritdoc}
     */
    public function clearFailures(): bool
    {
        $this->failures = 0;

        return true;
    }

    /**
     * Helper to create a transaction from the Place.
     *
     * @param Place $place the Circuit Breaker place
     * @param Service $service the service URI
     *
     * @throws InvalidTransaction
     */
    public static function createFromPlace(Place $place, Service $service): self
    {
        $threshold = $place->getThreshold();

        return new self(
            $service,
            0,
            $place->getState(),
            $threshold
        );
    }

    /**
     * Set the right DateTime from the threshold value.
     *
     * @param int $threshold the Transaction threshold (in ms)
     *
     * @throws \Exception
     */
    private function initThresholdDateTime(int $threshold): void
    {
        $thresholdDateTime = new DateTime();
        $thresholdInSeconds = $threshold / 1000;
        $thresholdDateTime->modify("+$thresholdInSeconds second");

        $this->thresholdDateTime = $thresholdDateTime;
    }

    /**
     * Ensure the transaction is valid.
     *
     * @param Service $service the service
     * @param int $failures the failures should be a positive value
     * @param string $state the Circuit Breaker state
     * @param int $threshold the threshold should be a positive value (in ms)
     *
     * @throws InvalidTransaction
     */
    private function validate(Service $service, int $failures, string $state, int $threshold): bool
    {
        $assertionsAreValid = Assert::isAService($service)
            && Assert::isPositiveInteger($failures)
            && Assert::isString($state)
            && Assert::isPositiveInteger($threshold);

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidTransaction::invalidParameters($service, $failures, $state, $threshold);
    }
}
