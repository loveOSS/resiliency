<?php

namespace Tests\Resiliency\Events;

use Resiliency\Events\AvailabilityChecked;
use Resiliency\Events\Closed;
use Resiliency\Events\Initiated;
use Resiliency\Events\Isolated;
use Resiliency\Events\Opened;
use Resiliency\Events\ReOpened;
use Resiliency\Events\Reseted;
use Resiliency\Events\Tried;

class EventsTest extends TransitionEventTestCase
{
    /**
     * @param string $eventClass the Event class name
     * @dataProvider getAllEvents
     */
    public function testAllEventsAreValid(string $eventClass): void
    {
        $this->checkEventIsValid($eventClass);
    }

    /**
     * @return array the list of all events
     */
    public function getAllEvents(): array
    {
        return [
            'opened' => [Opened::class],
            'closed' => [Closed::class],
            'initiated' => [Initiated::class],
            'isolated' => [Isolated::class],
            'reopened' => [ReOpened::class],
            'reseted' => [Reseted::class],
            'tried' => [Tried::class],
            'availability_checked' => [AvailabilityChecked::class],
        ];
    }
}
