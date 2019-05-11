<?php

namespace Resiliency\Transactions;

use DateTime;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\InvalidTransaction;
use Resiliency\Utils\Assert;

/**
 * Main implementation of Circuit Breaker transaction.
 */
final class SimpleTransaction implements Transaction
{
    /**
     * @var string the URI of the service
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
     * @param string $service the service URI
     * @param int $failures the allowed failures
     * @param string $state the circuit breaker state/place
     * @param float $threshold the place threshold
     */
    public function __construct(string $service, int $failures, string $state, float $threshold)
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
    public function getService(): string
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
    public function incrementFailures(): bool
    {
        ++$this->failures;

        return true;
    }

    /**
     * Helper to create a transaction from the Place.
     *
     * @param Place $place the Circuit Breaker place
     * @param string $service the service URI
     *
     * @return self
     */
    public static function createFromPlace(Place $place, $service): self
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
     * @param float $threshold the Transaction threshold
     *
     * @throws \Exception
     */
    private function initThresholdDateTime(float $threshold): void
    {
        $thresholdDateTime = new DateTime();
        $thresholdDateTime->modify("+$threshold second");

        $this->thresholdDateTime = $thresholdDateTime;
    }

    /**
     * Ensure the transaction is valid.
     *
     * @param string $service the service URI
     * @param int $failures the failures should be a positive value
     * @param string $state the Circuit Breaker state
     * @param float $threshold the threshold should be a positive value
     *
     * @return bool
     *
     * @throws InvalidTransaction
     */
    private function validate($service, $failures, $state, $threshold): bool
    {
        $assertionsAreValid = Assert::isURI($service)
            && Assert::isPositiveInteger($failures)
            && Assert::isString($state)
            && Assert::isPositiveValue($threshold)
        ;

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidTransaction::invalidParameters($service, $failures, $state, $threshold);
    }
}
