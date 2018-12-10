<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\CircuitBreaker;

final class SimpleCircuitBreaker implements CircuitBreaker
{
    /**
     * @var string The service name.
     */
    private $service;

    /**
     * @var Place The current Circuit Breaker place.
     */
    private $currentPlace;

    /**
     * @var Place[] The Circuit Breaker places.
     */
    private $places = [];

    /**
     * Constructor
     */
    public function __construct(Place $openPlace, Place $halfOpenPlace, Place $closePlace)
    {
        $this->currentPlace = $closedPlace;
        $this->places = [$closePlace, $halfOpenPlace, $openPlace];
    }

    /**
     * {@inheritdoc}
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function getFailures()
    {
        return $this->currentPlace->getFailures();
    }

    /**
     * {@inheritdoc}
     */
    public function call($service)
    {
        // implement the right workflow with machine state.
        try {
            return $this->currentPlace->run($service);
        } catch (UnavailableService $exception) {
            // ...
        }
    }
}
