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
    public static function isPositiveValue($value)
    {
        return !\is_string($value) && is_numeric($value) && $value >= 0;
    }

    /**
     * @param mixed $value the value to evaluate
     *
     * @return bool
     */
    public static function isPositiveInteger($value)
    {
        return self::isPositiveValue($value) && \is_int($value);
    }

    /**
     * @param mixed $value the value to evaluate
     *
     * @return bool
     */
    public static function isURI($value)
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
    public static function isString($value)
    {
        return !empty($value) && \is_string($value);
    }
}
