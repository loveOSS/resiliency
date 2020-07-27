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
     * @param string $parameter the parameter to evaluate
     * @param mixed $valueToFormat the value to format
     * @param string $validationFunction the validation function
     * @param string $expectedType the expected type
     */
    public static function format(
        string $parameter,
        $valueToFormat,
        string $validationFunction,
        string $expectedType
    ): string {
        $errorMessage = '';
        $isValid = (bool) Assert::$validationFunction($valueToFormat);
        $type = \gettype($valueToFormat);
        $hasStringValue = \in_array($type, ['integer', 'float', 'string'], true);

        if (!$isValid) {
            $errorMessage = sprintf(
                'Excepted %s to be %s, got %s',
                $parameter,
                $expectedType,
                $type
            );

            if ($hasStringValue) {
                $errorMessage .= sprintf(' (%s)', (string) $valueToFormat);
            }

            $errorMessage .= PHP_EOL;
        }

        return $errorMessage;
    }
}
