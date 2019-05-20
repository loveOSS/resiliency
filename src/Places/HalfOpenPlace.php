<?php

namespace Resiliency\Places;

use Resiliency\States;

final class HalfOpenPlace extends AbstractPlace
{
    /**
     * @param int   $failures  the Place failures
     * @param float $timeout   the Place timeout
     * @param float $threshold the Place threshold
     */
    public function __construct(int $failures, float $timeout, float $threshold)
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
