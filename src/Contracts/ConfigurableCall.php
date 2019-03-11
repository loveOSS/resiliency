<?php

namespace PrestaShop\CircuitBreaker\Contracts;

interface ConfigurableCall
{
    /**
     * The function that execute the service.
     *
     * @param string the service to call
     * @param callable $fallback if the service is unavailable, rely on the fallback
     * @param array $serviceParameters the parameters to include
     *
     * @return string
     */
    public function callWithParameters(
        $service,
        callable $fallback,
        array $serviceParameters = []
    );
}
