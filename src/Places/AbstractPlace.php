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
     * @var int the Place threshold
     */
    private $threshold;

    /**
     * @param int $failures the Place failures
     * @param float $timeout the Place timeout
     * @param int $threshold the Place threshold
     */
    public function __construct($failures, $timeout, $threshold)
    {
        $this->validate($failures, $timeout, $threshold);

        $this->failures = $failures;
        $this->timeout = $timeout;
        $this->threshold = $threshold;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getState();

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
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Helper: create a Place from an array.
     *
     * @var array the failures, timeout and treshold
     *
     * @return self
     */
    public static function fromArray(array $settings)
    {
        return new static($settings[0], $settings[1], $settings[2]);
    }

    /**
     * Ensure the place is valid (PHP5 is permissive).
     *
     * @param int $failures the failures should be a positive value
     * @param float $timeout the timeout should be a positive value
     * @param int $threshold the threshold should be a positive value
     *
     * @throws InvalidPlace
     *
     * @return bool true if valid
     */
    private function validate($failures, $timeout, $threshold)
    {
        $assertionsAreValid = Assert::isPositiveInteger($failures)
            && Assert::isPositiveValue($timeout)
            && Assert::isPositiveInteger($threshold)
        ;

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidPlace::invalidSettings($failures, $timeout, $threshold);
    }
}
