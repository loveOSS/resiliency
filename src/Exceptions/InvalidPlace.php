<?php

namespace PrestaShop\CircuitBreaker\Exceptions;

use Exception;

final class InvalidPlace extends Exception
{
    /**
     * @param mixed $failures the failures
     * @param mixed $timeout the timeout
     * @param mixed $treshold the treshold
     */
    public static function invalidSettings($failures, $timeout, $treshold)
    {
        $exceptionMessage = 'Invalid settings for Place' . PHP_EOL;
        $exceptionMessage .= self::formatError('failures', $failures);
        $exceptionMessage .= self::formatError('timeout', $timeout);
        $exceptionMessage .= self::formatError('treshold', $treshold);

        return new self($exceptionMessage);
    }

    /**
     * Format error message
     *
     * @param string $parameter the parameter to evaluate
     * @param mixed $value the value to format
     *
     * @return string
     */
    private static function formatError($parameter, $value)
    {
        $type = gettype($value);

        $hasStringValue = in_array($type, ['integer', 'float', 'string']);
        $errorMessage = sprintf(
            'Excepted %s to be positive integer, got %s',
            $parameter,
            $type
        );

        if ($hasStringValue) {
            $errorMessage .= sprintf(' (%s)', (string) $value);
        }

        $errorMessage .= PHP_EOL;

        return $errorMessage;
    }
}
