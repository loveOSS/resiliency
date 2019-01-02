<?php

namespace PrestaShop\CircuitBreaker\Contracts;

interface CircuitBreakerFactory
{
    public function create($fallback = null);

    public function createFactory($settings = []);
}
