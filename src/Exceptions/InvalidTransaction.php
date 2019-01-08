<?php

namespace PrestaShop\CircuitBreaker\Exceptions;

use Exception;
use PrestaShop\CircuitBreaker\Utils\ErrorFormatter;

final class InvalidTransaction extends Exception
{
    /**
     * @param mixed $service the service URI
     * @param mixed $failures the failures
     * @param mixed $state the Circuit Breaker
     * @param mixed $threshold the threshold
     *
     * @return self
     */
    public static function invalidParameters($service, $failures, $state, $threshold)
    {
        $exceptionMessage = 'Invalid parameters for Transaction' . PHP_EOL .
            ErrorFormatter::format('service', $service, 'isURI', 'an URI') .
            ErrorFormatter::format('failures', $failures, 'isPositiveInteger', 'a positive integer') .
            ErrorFormatter::format('state', $state, 'isString', 'a string') .
            ErrorFormatter::format('threshold', $threshold, 'isPositiveInteger', 'a positive integer')
        ;

        return new self($exceptionMessage);
    }
}
