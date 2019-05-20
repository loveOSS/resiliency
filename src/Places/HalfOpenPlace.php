<?php

namespace Resiliency\Places;

use Resiliency\States;

/**
 * When the circuit is half-open:
 *
 * the next action will be treated as a trial, to determine the circuit's health.
 *
 * If this call throws a handled exception, that exception is rethrown,
 * and the circuit transitions immediately back to open, and remains open again for the configured timespan.
 *
 * If the call throws no exception, the circuit transitions back to closed.
 */
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
