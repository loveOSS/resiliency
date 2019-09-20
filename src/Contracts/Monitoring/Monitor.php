<?php

namespace Resiliency\Contracts\Monitoring;

use Resiliency\Contracts\Event;

/**
 * If you need to monitor and collect information about the circuit breaker
 * use the monitor to collect the information when events are dispatched.
 */
interface Monitor
{
    /**
     * @param Event $event the dispatched event
     */
    public function collect(Event $event): void;

    /**
     * @return Report the Monitor Report
     */
    public function getReport(): Report;
}
