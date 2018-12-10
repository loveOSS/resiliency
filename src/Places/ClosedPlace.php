<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\States;

final class ClosedPlace implements Place
{
    /**
     * {@inheritdoc}
     */
    public function run(callable $callable)
    {
        // TBD
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return States::CLOSED_STATE;
    }
}
