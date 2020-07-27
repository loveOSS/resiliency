<?php

namespace Resiliency\Exceptions;

use Exception;
use Resiliency\Contracts\Exception as ResiliencyException;
use Resiliency\Utils\ErrorFormatter;

final class InvalidTransaction extends Exception implements ResiliencyException
{
    /**
     * @param mixed $serviceURI the service URI
     * @param mixed $failures the failures
     * @param mixed $state the Circuit Breaker
     * @param mixed $threshold the threshold
     */
    public static function invalidParameters($serviceURI, $failures, $state, $threshold): self
    {
        $exceptionMessage = 'Invalid parameters for Transaction' . PHP_EOL .
            ErrorFormatter::format('service', $serviceURI, 'isAService', 'an instance of Service') .
            ErrorFormatter::format('failures', $failures, 'isPositiveInteger', 'a positive integer') .
            ErrorFormatter::format('state', $state, 'isString', 'a string') .
            ErrorFormatter::format('threshold', $threshold, 'isPositiveInteger', 'a positive integer');

        return new self($exceptionMessage);
    }
}
