<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\States;

final class OpenPlace extends AbstractPlace
{
    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return States::OPEN_STATE;
    }
}
