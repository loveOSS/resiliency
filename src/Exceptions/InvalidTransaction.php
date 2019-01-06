<?php

namespace PrestaShop\CircuitBreaker\Exceptions;

use PrestaShop\CircuitBreaker\Utils\Assert;
use Exception;

final class InvalidTransaction extends Exception
{
    /**
     * @param mixed $service the service URI
     * @param mixed $failures the failures
     * @param mixed $state the Circuit Breaker
     * @param mixed $threshold the threshold
     */
    public static function invalidParameters($service, $failures, $state, $threshold)
    {
        $exceptionMessage = 'Invalid parameters for Transaction' . PHP_EOL;
        $exceptionMessage .= self::formatError('service', $service, 'isURI', 'string');
        $exceptionMessage .= self::formatError('failures', $failures, 'isPositiveValue', 'positive integer');
        $exceptionMessage .= self::formatError('state', $state, 'isString', 'string');
        $exceptionMessage .= self::formatError('threshold', $threshold, 'isPositiveValue', 'positive float');

        return new self($exceptionMessage);
    }

    /**
     * Format error message
     *
     * @param string $parameter the parameter to evaluate
     * @param mixed $value the value to format
     * @param string $function the validation function
     * @param string $expectedType the expected type
     *
     * @return string
     */
    private static function formatError($parameter, $value, $function, $expectedType)
    {
        $errorMessage = '';
        $isValid = Assert::$function($value);
        $type = gettype($value);
        $hasStringValue = in_array($type, ['integer', 'float', 'string']);

        if (!$isValid) {
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
        }

        return $errorMessage;
    }
}
