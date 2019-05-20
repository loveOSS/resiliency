<?php

namespace Resiliency\Places;

use Resiliency\Contracts\Place;
use Resiliency\Exceptions\InvalidPlace;
use Resiliency\Utils\Assert;

abstract class AbstractPlace implements Place
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
     * @param int   $failures  the Place failures
     * @param float $timeout   the Place timeout
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
     * Helper: create a Place from an array.
     *
     * @var array the failures, timeout and threshold
     *
     * @return self
     */
    public static function fromArray(array $settings): self
    {
        return new static((int) $settings[0], (float) $settings[1], (float) $settings[2]);
    }

    /**
     * Ensure the place is valid
     *
     * @param int   $failures  the failures should be a positive value
     * @param float $timeout   the timeout should be a positive value
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
