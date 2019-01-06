<?php

namespace PrestaShop\CircuitBreaker\Exceptions;

use Exception;

final class InvalidPlace extends Exception
{
    /**
     * @param mixed $failures the failures
     * @param mixed $timeout the timeout
     * @param mixed $threshold the threshold
     */
    public static function invalidSettings($failures, $timeout, $threshold)
    {
        $exceptionMessage = 'Invalid settings for Place' . PHP_EOL;
        $exceptionMessage .= self::formatError('failures', $failures, 'integer');
        $exceptionMessage .= self::formatError('timeout', $timeout, 'float');
        $exceptionMessage .= self::formatError('threshold', $threshold, 'integer');

        return new self($exceptionMessage);
    }

    /**
     * Format error message
     *
     * @param string $parameter the parameter to evaluate
     * @param mixed $value the value to format
     * @param string $expectedType the expected type
     *
     * @return string
     */
    private static function formatError($parameter, $value, $expectedType)
    {
        $type = gettype($value);

        $hasStringValue = in_array($type, ['integer', 'float', 'string']);
        $errorMessage = sprintf(
            'Excepted %s to be %s, got %s',
            $parameter,
            $expectedType,
            $type
        );

        if ($hasStringValue) {
            $errorMessage .= sprintf(' (%s)', (string) $value);
        }

        $errorMessage .= PHP_EOL;

        return $errorMessage;
    }
}
