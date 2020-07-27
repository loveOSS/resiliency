<?php

namespace Resiliency\Transactions;

use DateTime;
use Exception;
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
    private Service $service;
    private int $failures;
    private string $state;
    private DateTime $thresholdDateTime;

    /**
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
     * @throws Exception
     */
    private function initThresholdDateTime(int $threshold): void
    {
        $thresholdDateTime = new DateTime();
        $thresholdInSeconds = $threshold / 1_000;
        $thresholdDateTime->modify("+$thresholdInSeconds second");

        $this->thresholdDateTime = $thresholdDateTime;
    }

    /**
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
