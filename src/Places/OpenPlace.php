<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\States;

final class OpenPlace implements Place
{
    /**
     * {@inheritdoc}
     */
    public function run($service)
    {
        // TBD
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return States::OPEN_STATE;
    }
}
