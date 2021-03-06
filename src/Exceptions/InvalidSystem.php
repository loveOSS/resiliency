<?php

namespace Resiliency\Exceptions;

use Exception;
use Resiliency\Contracts\Exception as ResiliencyException;

final class InvalidSystem extends Exception implements ResiliencyException
{
    private const REQUIRED_SETTINGS = [
        'timeout',
        'stripped_timeout',
        'failures',
        'threshold',
    ];

    public static function missingSettings(array $systemSettings): self
    {
        $exceptionMessage = 'Missing settings for System:' . PHP_EOL;

        foreach (self::REQUIRED_SETTINGS as $setting) {
            if (!array_key_exists($setting, $systemSettings)) {
                $exceptionMessage .= sprintf(
                    'The setting "%s" is missing from configuration',
                    $setting
                ) . PHP_EOL;
            }
        }

        return new self($exceptionMessage);
    }

    public static function phpTimeoutExceeded(): self
    {
        $exceptionMessage = 'The configuration timeout exceeds the PHP timeout' . PHP_EOL;
        $exceptionMessage .= sprintf(
            'Configure `max_execution_time` to a higher value, got: "%ss".',
            ini_get('max_execution_time')
        );

        return new self($exceptionMessage);
    }
}
