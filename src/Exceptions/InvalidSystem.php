<?php

namespace Resiliency\Exceptions;

use Exception;
use Resiliency\Contracts\Exception as ResiliencyException;

final class InvalidSystem extends Exception implements ResiliencyException
{
    const REQUIRED_SETTINGS = [
        'timeout',
        'stripped_timeout',
        'failures',
        'threshold',
    ];

    /**
     * @param array $settings the System settings
     *
     * @return self
     */
    public static function missingSettings(array $settings): self
    {
        $exceptionMessage = 'Missing settings for System:' . PHP_EOL;

        foreach (self::REQUIRED_SETTINGS as $setting) {
            if (!array_key_exists($setting, $settings)) {
                $exceptionMessage .= sprintf('The setting "%s" is missing from configuration', $setting);
            }
        }

        return new self($exceptionMessage);
    }
}
