<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Exceptions\InvalidPlace;

abstract class AbstractPlace implements Place
{
    private $failures;
    private $timeout;
    private $treshold;

    public function __construct($failures, $timeout, $treshold)
    {
        if ($this->validate($failures, $timeout, $treshold)) {
            $this->failures = $failures;
            $this->timeout = $timeout;
            $this->treshold = $treshold;
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
    public function getTreshold()
    {
        return $this->treshold;
    }

    /**
     * Ensure the place is valid (PHP5 is permissive)
     *
     * @param int $failures the failures should be a positive integer
     * @param int $timeout the timeout should be a positive integer
     * @param int $treshold the treshold should be a positive integer
     *
     * @throws InvalidPlace
     *
     * @return bool true if valid
     */
    private function validate($failures, $timeout, $treshold)
    {
        $isPositiveInteger = function ($value) {
            return is_numeric($value) && $value >= 0;
        };

        if (
            $isPositiveInteger($failures) &&
            $isPositiveInteger($timeout) &&
            $isPositiveInteger($treshold)
            ) {
            return true;
        }
        throw InvalidPlace::invalidSettings($failures, $timeout, $treshold);
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
