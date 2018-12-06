<?php

namespace PrestaShop\Contracts\CircuitBreaker;

interface Place
{
    /**
     * Execute the function to evaluate.
     *
     * @var callable $callable function to evaluate
     */
    public function run(callable $callable);

    /**
     * Return the current state of the Circuit Breaker.
     *
     * @return string
     */
    public function getState();
}