<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Contracts\Place;

abstract class AbstractPlace implements Place
{
    private $failures;
    private $timeout;
    private $treshold;

    public function __construct($failures, $timeout, $treshold)
    {
        $this->failures = $failures;
        $this->timeout = $timeout;
        $this->treshold = $treshold;
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
}
