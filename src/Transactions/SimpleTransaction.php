<?php

namespace Resiliency\Transactions;

use DateTime;
use Psr\Http\Message\RequestInterface;
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
     * @var RequestInterface the request
     */
    private $request;

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
     * @param RequestInterface $request the request
     * @param int $failures the allowed failures
     * @param string $state the circuit breaker state/place
     * @param float $threshold the place threshold
     *
     * @throws InvalidTransaction
     */
    public function __construct(RequestInterface $request, int $failures, string $state, float $threshold)
    {
        $this->validate($request, $failures, $state, $threshold);

        $this->request = $request;
        $this->failures = $failures;
        $this->state = $state;
        $this->initThresholdDateTime($threshold);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
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
     * @return self
     *
     * @throws InvalidTransaction
     */
    public static function createFromPlace(Place $place, RequestInterface $request): self
    {
        $threshold = $place->getThreshold();

        return new self(
            $request,
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
     * @param RequestInterface $request the request
     * @param int $failures the failures should be a positive value
     * @param string $state the Circuit Breaker state
     * @param float $threshold the threshold should be a positive value
     *
     * @return bool
     *
     * @throws InvalidTransaction
     */
    private function validate(RequestInterface $request, int $failures, string $state, float $threshold): bool
    {
        $assertionsAreValid = Assert::isARequest($request)
            && Assert::isPositiveInteger($failures)
            && Assert::isString($state)
            && Assert::isPositiveValue($threshold);

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidTransaction::invalidParameters($request, $failures, $state, $threshold);
    }
}
