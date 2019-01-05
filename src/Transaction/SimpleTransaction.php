<?php

namespace PrestaShop\CircuitBreaker\Transaction;

use PrestaShop\CircuitBreaker\Contracts\Transaction;
use PrestaShop\CircuitBreaker\Contracts\Place;
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
     * @var int
     */
    private $thresholdDateTime;

    public function __construct($service, $failures, $state, $threshold)
    {
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
    public function resetThresholdDateTime($thresholdDateTime)
    {
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
     * @var Place $place the Circuit Breaker place
     * @var string $service the service URI
     *
     * @return self
     */
    public static function createFromPlace(Place $place, $service)
    {
        $thresholdDateTime = new DateTime();
        $threshold = $place->getThreshold();
        $thresholdDateTime->modify("+$threshold second");

        return new self(
            $service,
            0,
            $place->getState(),
            $thresholdDateTime
        );
    }
}
