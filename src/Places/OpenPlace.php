<?php

namespace Resiliency\Places;

use Resiliency\States;

final class OpenPlace extends AbstractPlace
{
    /**
     * @param int   $failures  the Place failures
     * @param float $timeout   the Place timeout
     * @param float $threshold the Place threshold
     */
    public function __construct(int $failures, float $timeout, float $threshold)
    {
        parent::__construct(0, 0, $threshold);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return States::OPEN_STATE;
    }
}
