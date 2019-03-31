<?php

namespace Resiliency\Places;

use Resiliency\States;

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
