<?php

namespace PrestaShop\CircuitBreaker\Exceptions;

use Exception;
use PrestaShop\CircuitBreaker\Utils\ErrorFormatter;

final class InvalidPlace extends Exception
{
    /**
     * @param mixed $failures the failures
     * @param mixed $timeout the timeout
     * @param mixed $threshold the threshold
     *
     * @return self
     */
    public static function invalidSettings($failures, $timeout, $threshold)
    {
        $exceptionMessage = 'Invalid settings for Place' . PHP_EOL .
            ErrorFormatter::format('failures', $failures, 'isPositiveInteger', 'a positive integer') .
            ErrorFormatter::format('timeout', $timeout, 'isPositiveValue', 'a float') .
            ErrorFormatter::format('threshold', $threshold, 'isPositiveInteger', 'a positive integer')
        ;

        return new self($exceptionMessage);
    }
}
