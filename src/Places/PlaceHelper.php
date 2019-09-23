<?php

namespace Resiliency\Places;

use Nyholm\Psr7\Request;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Transaction;
use Resiliency\Contracts\Place;
use Resiliency\Contracts\Event;
use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Utils\Assert;
use DateTime;

abstract class PlaceHelper implements Place
{
    /**
     * @var int the Place failures
     */
    private $failures;

    /**
     * @var float the Place timeout
     */
    private $timeout;

    /**
     * @var float the Place threshold
     */
    private $threshold;

    /**
     * @var CircuitBreaker the Circuit Breaker
     */
    protected $circuitBreaker;

    /**
     * @param int $failures the Place failures
     * @param float $timeout the Place timeout
     * @param float $threshold the Place threshold
     */
    public function __construct(int $failures, float $timeout, float $threshold)
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
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getThreshold(): float
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
        $service = $transaction->getRequest();

        return $this->circuitBreaker->call($service->getURI(), $fallback, $service->getParameters());
    }

    /**
     * @param Transaction $transaction the Transaction
     *
     * @return bool
     */
    public function isAllowedToRetry(Transaction $transaction): bool
    {
        return $transaction->getFailures() < $this->failures;
    }

    /**
     * @param Transaction $transaction the Transaction
     *
     * @return bool
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
     * @return string the configured fallback
     */
    protected function useFallback(Transaction $transaction, callable $fallback): string
    {
        $service = $transaction->getRequest();

        return (string) $fallback($service);
    }

    /**
     * Ensure the place is valid
     *
     * @param int $failures the failures should be a positive value
     * @param float $timeout the timeout should be a positive value
     * @param float $threshold the threshold should be a positive value
     *
     * @throws InvalidPlace
     *
     * @return bool true if valid
     */
    private function validate(int $failures, float $timeout, float $threshold): bool
    {
        $assertionsAreValid = Assert::isPositiveInteger($failures)
            && Assert::isPositiveValue($timeout)
            && Assert::isPositiveValue($threshold);

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidPlace::invalidSettings($failures, $timeout, $threshold);
    }
}
