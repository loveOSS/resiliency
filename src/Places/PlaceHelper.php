<?php

namespace Resiliency\Places;

use DateTime;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Event;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\Transaction;
use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Utils\Assert;

abstract class PlaceHelper implements Place
{
    /**
     * @var int the Place failures
     */
    private $failures;

    /**
     * @var int the Place timeout
     */
    private $timeout;

    /**
     * @var int the Place threshold
     */
    private $threshold;

    /**
     * @var CircuitBreaker the Circuit Breaker
     */
    protected $circuitBreaker;

    /**
     * @param int $failures the Place failures
     * @param int $timeout the Place timeout
     * @param int $threshold the Place threshold
     */
    public function __construct(int $failures, int $timeout, int $threshold)
    {
        $this->validate($failures, $timeout, $threshold);

        $this->failures = $failures;
        $this->timeout = $timeout;
        $this->threshold = $threshold;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getState(): string;

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
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    /**
     * {@inheritdoc}
     */
    public function setCircuitBreaker(CircuitBreaker $circuitBreaker): Place
    {
        $this->circuitBreaker = $circuitBreaker;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function call(Transaction $transaction, callable $fallback): string
    {
        $service = $transaction->getService();

        return $this->circuitBreaker->call($service->getURI(), $fallback, $service->getParameters());
    }

    /**
     * @param Transaction $transaction the Transaction
     */
    public function isAllowedToRetry(Transaction $transaction): bool
    {
        return $transaction->getFailures() < $this->failures;
    }

    /**
     * @param Transaction $transaction the Transaction
     */
    public function haveWaitedLongEnough(Transaction $transaction): bool
    {
        return $transaction->getThresholdDateTime() < new DateTime();
    }

    /**
     * Helper to dispatch transition events.
     *
     * @param Event $event the circuit breaker event
     */
    protected function dispatch(Event $event): void
    {
        $this->circuitBreaker
            ->getDispatcher()
            ->dispatch($event)
        ;
    }

    /**
     * Helper to return the fallback Response.
     *
     * @return string the configurated fallback
     */
    protected function useFallback(Transaction $transaction, callable $fallback): string
    {
        $service = $transaction->getService();

        return (string) $fallback($service);
    }

    /**
     * Ensure the place is valid
     *
     * @param int $failures the failures should be a positive value
     * @param int $timeout the timeout should be a positive value
     * @param int $threshold the threshold should be a positive value
     *
     * @throws InvalidPlace
     *
     * @return bool true if valid
     */
    private function validate(int $failures, int $timeout, int $threshold): bool
    {
        $assertionsAreValid = Assert::isPositiveInteger($failures)
            && Assert::isPositiveInteger($timeout)
            && Assert::isPositiveInteger($threshold);

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidPlace::invalidSettings($failures, $timeout, $threshold);
    }
}
