<?php

namespace Resiliency\Places;

use Resiliency\States;

final class HalfOpenPlace extends AbstractPlace
{
    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return States::HALF_OPEN_STATE;
    }
}
