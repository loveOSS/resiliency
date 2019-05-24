<?php

namespace Resiliency\Places;

use Resiliency\States;

/**
 * The circuit initially starts closed. When the circuit is closed:
 *
 * The circuit-breaker executes actions placed through it, measuring the failures and successes of those actions.
 * If the failures exceed a certain threshold, the circuit will break (open).
 */
final class ClosedPlace extends AbstractPlace
{
    /**
     * @param int $failures the Place failures
     * @param float $timeout the Place timeout
     */
    public function __construct(int $failures, float $timeout)
    {
        parent::__construct($failures, $timeout, 0.0);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return States::CLOSED_STATE;
    }
}
