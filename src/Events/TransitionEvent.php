<?php

namespace PrestaShop\CircuitBreaker\Events;

use Symfony\Component\EventDispatcher\Event;

class TransitionEvent extends Event
{
    private $eventName;
    private $service;
    private $parameters;

    public function __construct($eventName, $service, array $parameters)
    {
        $this->eventName = $eventName;
        $this->service = $service;
        $this->parameters = $parameters;
    }

    public function getEvent()
    {
        return $this->eventName;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
