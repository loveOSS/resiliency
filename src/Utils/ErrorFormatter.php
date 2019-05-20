<?php

namespace Resiliency\Utils;

/**
 * Helper to provide complete and easy to read
 * error messages.
 * Mostly used to build Exceptions messages.
 */
final class ErrorFormatter
{
    /**
     * Format error message.
     *
     * @param string $parameter    the parameter to evaluate
     * @param mixed  $value        the value to format
     * @param string $function     the validation function
     * @param string $expectedType the expected type
     *
     * @return string
     */
    public static function format($parameter, $value, $function, $expectedType)
    {
        $errorMessage = '';
        $isValid = (bool) Assert::$function($value);
        $type = \gettype($value);
        $hasStringValue = \in_array($type, ['integer', 'float', 'string'], true);

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
