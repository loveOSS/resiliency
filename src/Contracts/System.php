<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * The System define the places available
 * for the Circuit Breaker and the initial Place.
 */
interface System
{
    /**
     * @return Place[] the list of places of the system
     */
    public function getPlaces();

    /**
     * @return Place the initial place of the system
     */
    public function getInitialPlace();
}
