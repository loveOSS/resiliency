<?php

namespace Resiliency\Places;

use Resiliency\States;

final class ClosedPlace extends AbstractPlace
{
    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return States::CLOSED_STATE;
    }
}
