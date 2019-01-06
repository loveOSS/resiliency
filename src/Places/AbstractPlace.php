<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Exceptions\InvalidPlace;

abstract class AbstractPlace implements Place
{
    private $failures;
    private $timeout;
    private $threshold;

    public function __construct($failures, $timeout, $threshold)
    {
        if ($this->validate($failures, $timeout, $threshold)) {
            $this->failures = $failures;
            $this->timeout = $timeout;
            $this->threshold = $threshold;
        }
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
     * Ensure the place is valid (PHP5 is permissive)
     *
     * @param int $failures the failures should be a positive value
     * @param int $timeout the timeout should be a positive value
     * @param int $threshold the threshold should be a positive value
     *
     * @throws InvalidPlace
     *
     * @return bool true if valid
     */
    private function validate($failures, $timeout, $threshold)
    {
        $isPositiveValue = function ($value) {
            return is_numeric($value) && $value >= 0;
        };

        if (
            $isPositiveValue($failures) &&
            $isPositiveValue($timeout) &&
            $isPositiveValue($threshold)
            ) {
            return true;
        }
        throw InvalidPlace::invalidSettings($failures, $timeout, $threshold);
    }

    /**
     * Helper: create a Place from an array
     *
     * @var array the failures, timeout and treshold
     *
     * @return self
     */
    public static function fromArray(array $settings)
    {
        return new static($settings[0], $settings[1], $settings[2]);
    }
}
