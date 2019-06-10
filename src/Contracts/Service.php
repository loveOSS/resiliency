<?php

namespace Resiliency\Contracts;

/**
 * Describe what is a service for the Resiliency library.
 */
interface Service
{
    /**
     * @return string the URI we try to reach
     */
    public function getURI(): string;

    /**
     * @return array the URI parameters
     */
    public function getParameters(): array;
}
