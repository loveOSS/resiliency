<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\States;

final class Places implements Circuit
{
    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return States::CLOSED_STATE;
    }
}
