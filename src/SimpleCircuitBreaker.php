<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Exceptions\UnavailableService;
use PrestaShop\CircuitBreaker\Contracts\CircuitBreaker;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;
use PrestaShop\CircuitBreaker\Contracts\Place;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker implements CircuitBreaker
{
    /**
     * @var string the service name
     */
    private $service;

    /**
     * @var Place the current Circuit Breaker place
     */
    private $currentPlace;

    /**
     * @var Place[] the Circuit Breaker places
     */
    private $places = [];

    /**
     * Constructor
     */
    public function __construct(Place $openPlace, Place $halfOpenPlace, Place $closedPlace)
    {
        $this->currentPlace = $closedPlace;
        $this->places = [
            States::CLOSED_STATE => $closedPlace,
            States::HALF_OPEN_STATE => $halfOpenPlace,
            States::OPEN_STATE => $openPlace,
        ];
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
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * {@inheritdoc}
     */
    public function call($service, callable $fallback)
    {
        // implement the right workflow with a machine state.
        // see schema.

        try {
            if ($this->isOpened()) {
                // @todo: once the treshold is reached
                // $this->moveToState(States::HALF_OPEN_STATE);
                return call_user_func($fallback);
            }

            return $this->tryExecute($service);

            if ($this->isHalfOpened()) {
                $this->currentPlace = $this->places[States::CLOSED_STATE];
            }
        } catch (UnavailableService $exception) {
            if ($this->isHalfOpened()) {
                $this->moveStateTo(States::OPEN_STATE);
            }

            // @todo: increment failures++ and save
            if ($this->isClosed()) {
                $this->moveStateTo(States::OPEN_STATE);
                // storage for waiting before retry

                $this->call($service, $fallback);
            }
        }
    }

    private function isOpened()
    {
        return $this->currentPlace->getState() === States::OPEN_STATE;
    }

    private function isHalfOpened()
    {
        return $this->currentPlace->getState() === States::HALF_OPEN_STATE;
    }

    private function isClosed()
    {
        return $this->currentPlace->getState() === States::CLOSED_STATE;
    }

    private function moveStateTo($state)
    {
        $this->currentPlace = $this->places[$state];
    }

    /**
     * @todo should be moved in its own class maybe?
     */
    private function tryExecute($service)
    {
        $client = new GuzzleClient();
        $response = $client->request(
            $service,
            [
            'method' => 'GET',
            'http_errors' => true,
            'connect_timeout' => $this->currentPlace->getTimeout(),
            'timeout' => $this->currentPlace->getTimeout(),
            ]
        );

        return $response;
    }
}
