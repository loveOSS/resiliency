<?php

namespace Resiliency\Places;

use Resiliency\States;

final class HalfOpenPlace extends AbstractPlace
{
    /**
     * @param float $timeout the Place timeout
     */
    public function __construct(float $timeout)
    {
        parent::__construct(0, $timeout, 0.0);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return States::HALF_OPEN_STATE;
    }
}
