<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\States;

final class HalfOpenPlace extends AbstractPlace
{
    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return States::HALF_OPEN_STATE;
    }
}
