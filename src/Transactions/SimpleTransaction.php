<?php

namespace PrestaShop\CircuitBreaker\Transactions;

use PrestaShop\CircuitBreaker\Exceptions\InvalidTransaction;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Utils\Assert;
use DateTime;

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

    public function __construct($service, $failures, $state, $threshold)
    {
        $this->validate($service, $failures, $state, $threshold);

        $this->service = $service;
        $this->failures = $failures;
        $this->state = $state;
        $this->resetThresholdDateTime($threshold);
    }

    /**
     * {@inheritdoc}
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getThresholdDateTime()
    {
        return $this->thresholdDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function resetFailures()
    {
        $this->failures = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function resetThresholdDateTime($threshold)
    {
        $thresholdDateTime = new DateTime();
        $thresholdDateTime->modify("+$threshold second");

        $this->thresholdDateTime = $thresholdDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function incrementFailures()
    {
        ++$this->failures;
    }

    /**
     * Helper to create a transaction from the Place.
     *
     * @var Place the Circuit Breaker place
     * @var string $service the service URI
     *
     * @return self
     */
    public static function createFromPlace(Place $place, $service)
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
     * Ensure the transaction is valid (PHP5 is permissive)
     *
     * @param string $service the service URI
     * @param int $failures the failures should be a positive value
     * @param string $state the Circuit Breaker state
     * @param int $threshold the threshold should be a positive value
     *
     * @throws InvalidTransaction
     *
     * @return bool true if valid
     */
    private function validate($service, $failures, $state, $threshold)
    {
        if (
            Assert::isURI($service) &&
            Assert::isPositiveInteger($failures) &&
            Assert::isString($state) &&
            Assert::isPositiveInteger($threshold)
            ) {
            return true;
        }

        throw InvalidTransaction::invalidParameters($service, $failures, $state, $threshold);
    }
}
