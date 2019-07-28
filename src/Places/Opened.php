<?php

namespace Resiliency\Places;

use Resiliency\Contracts\Transaction;
use Resiliency\Events\Opened as OpenedEvent;
use Resiliency\States;
use DateTime;

/**
 * While the circuit is in an open state: every call to the service
 * won't be executed and the fallback callback is executed.
 */
final class Opened extends AbstractPlace
{
    /**
     * @param float $threshold the Place threshold
     */
    public function __construct(float $threshold)
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

    /**
     * {@inheritdoc}
     */
    public function call(Transaction $transaction, callable $fallback): string
    {
        $service = $transaction->getService();
        $this->dispatch(new OpenedEvent($this->circuitBreaker, $service));

        if (!($transaction->getThresholdDateTime() < new DateTime())) {
            return (string) $fallback();
        }

        $this->circuitBreaker->moveStateTo(States::HALF_OPEN_STATE, $service);

        return parent::call($transaction, $fallback);
    }
}
