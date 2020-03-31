<?php

namespace Tests\Resiliency\Exceptions;

use PHPUnit\Framework\TestCase;
use Resiliency\Exceptions\InvalidSystem;

class InvalidSystemTest extends TestCase
{
    public function testCreation(): void
    {
        $invalidPlace = new InvalidSystem();

        self::assertInstanceOf(InvalidSystem::class, $invalidPlace);
    }

    /**
     * @dataProvider getSettings
     *
     * @param array $settings
     * @param string $expectedExceptionMessage
     */
    public function testInvalidSettings($settings, $expectedExceptionMessage): void
    {
        $invalidSystem = InvalidSystem::missingSettings($settings);

        self::assertSame($invalidSystem->getMessage(), $expectedExceptionMessage);
    }

    public function testPhpTimeoutExceeded(): void
    {
        $currentPhpTimeout = (string) ini_get('max_execution_time');
        ini_set('max_execution_time', '1');
        $invalidSystem = InvalidSystem::phpTimeoutExceeded();
        $expectedExceptionMessage = 'The configuration timeout exceeds the PHP timeout' . PHP_EOL;
        $expectedExceptionMessage .= 'Configure `max_execution_time` to a higher value, got: "1s".';

        self::assertSame($invalidSystem->getMessage(), $expectedExceptionMessage);
        ini_set('max_execution_time', $currentPhpTimeout);
    }

    public function getSettings(): array
    {
        return [
            'all_missing_settings' => [
                [],
                'Missing settings for System:' . PHP_EOL .
                'The setting "timeout" is missing from configuration' . PHP_EOL .
                'The setting "stripped_timeout" is missing from configuration' . PHP_EOL .
                'The setting "failures" is missing from configuration' . PHP_EOL .
                'The setting "threshold" is missing from configuration' . PHP_EOL,
            ],
            '2_missing_settings' => [
                [
                    'failures' => 1,
                    'timeout' => -1,
                ],
                'Missing settings for System:' . PHP_EOL .
                'The setting "stripped_timeout" is missing from configuration' . PHP_EOL .
                'The setting "threshold" is missing from configuration' . PHP_EOL,
            ],
            '1_missing_setting' => [
                [
                    'failures' => 1,
                    'timeout' => 1.0,
                    'stripped_timeout' => -1,
                ],
                'Missing settings for System:' . PHP_EOL .
                'The setting "threshold" is missing from configuration' . PHP_EOL,
            ],
            'all_present_settings' => [
                [
                    'failures' => 1,
                    'timeout' => 1.0,
                    'stripped_timeout' => 1.0,
                    'threshold' => 1.0,
                ],
                'Missing settings for System:' . PHP_EOL,
            ],
        ];
    }
}
