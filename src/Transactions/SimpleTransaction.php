<?php

namespace PrestaShop\CircuitBreaker\Transactions;

use DateTime;
use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Exceptions\InvalidTransaction;
use PrestaShop\CircuitBreaker\Utils\Assert;

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
     * @param int $threshold the place threshold
     */
    public function __construct($service, $failures, $state, $threshold)
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
    public function incrementFailures()
    {
        ++$this->failures;

        return true;
    }

    /**
     * Helper to create a transaction from the Place.
     *
     * @param Place the Circuit Breaker place
     * @param string $service the service URI
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
     * Set the right DateTime from the threshold value.
     *
     * @param int $threshold the Transaction threshold
     */
    private function initThresholdDateTime($threshold)
    {
        $thresholdDateTime = new DateTime();
        $thresholdDateTime->modify("+$threshold second");

        $this->thresholdDateTime = $thresholdDateTime;
    }

    /**
     * Ensure the transaction is valid (PHP5 is permissive).
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
        $assertionsAreValid = Assert::isURI($service)
            && Assert::isPositiveInteger($failures)
            && Assert::isString($state)
            && Assert::isPositiveInteger($threshold)
        ;

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidTransaction::invalidParameters($service, $failures, $state, $threshold);
    }
}
