<?php

namespace Resiliency\Places;

use Resiliency\Contracts\Transaction;
use Resiliency\Events\Opened as OpenedEvent;
use Resiliency\States;

/**
 * While the circuit is in an open state: every call to the service
 * won't be executed and the fallback callback is executed.
 */
final class Opened extends PlaceHelper
{
    public function __construct(int $threshold)
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

        if (!$this->haveWaitedLongEnough($transaction)) {
            return $this->useFallback($transaction, $fallback);
        }

        $this->circuitBreaker->moveStateTo(States::HALF_OPEN_STATE, $service);

        return parent::call($transaction, $fallback);
    }
}
