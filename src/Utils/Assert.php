<?php

namespace Resiliency\Utils;

/**
 * Utils class to handle object validation
 * Should be deprecated for most parts once
 * the library will drop PHP5 support.
 */
final class Assert
{
    /**
     * @param mixed $value the value to evaluate
     *
     * @return bool
     */
    public static function isPositiveValue($value): bool
    {
        return !\is_string($value) && is_numeric($value) && $value >= 0;
    }

    /**
     * @param mixed $value the value to evaluate
     *
     * @return bool
     */
    public static function isPositiveInteger($value): bool
    {
        return self::isPositiveValue($value) && \is_int($value);
    }

    /**
     * @param mixed $value the value to evaluate
     *
     * @return bool
     */
    public static function isURI($value): bool
    {
        return null !== $value
            && !is_numeric($value)
            && !\is_bool($value)
            && false !== filter_var($value, FILTER_SANITIZE_URL)
        ;
    }

    /**
     * @param mixed $value the value to evaluate
     *
     * @return bool
     */
    public static function isString($value): bool
    {
        return !empty($value) && \is_string($value);
    }
}
