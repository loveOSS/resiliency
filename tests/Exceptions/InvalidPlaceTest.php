<?php

namespace Tests\PrestaShop\CircuitBreaker\Exceptions;

use PrestaShop\CircuitBreaker\Exceptions\InvalidPlace;
use PHPUnit\Framework\TestCase;

class InvalidPlaceTest extends TestCase
{
    public function testCreation()
    {
        $invalidPlace = new InvalidPlace();

        $this->assertInstanceOf(InvalidPlace::class, $invalidPlace);
    }

    /**
     * @dataProvider getSettings
     *
     * @param array $settings
     * @param string $expectedExceptionMessage
     */
    public function testInvalidSettings($settings, $expectedExceptionMessage)
    {
        $invalidPlace = InvalidPlace::invalidSettings(
            $settings[0], // failures
            $settings[1], // timeout
            $settings[2]  // threshold
        );

        $this->assertSame($invalidPlace->getMessage(), $expectedExceptionMessage);
    }

    public function getSettings()
    {
        return [
            'all_invalid_settings' => [
                ['0', '1', null],
                'Invalid settings for Place' . PHP_EOL .
                'Excepted failures to be a positive integer, got string (0)' . PHP_EOL .
                'Excepted timeout to be a float, got string (1)' . PHP_EOL .
                'Excepted threshold to be a positive integer, got NULL' . PHP_EOL,
            ],
            '2_invalid_settings' => [
                [0, '1', null],
                'Invalid settings for Place' . PHP_EOL .
                'Excepted timeout to be a float, got string (1)' . PHP_EOL .
                'Excepted threshold to be a positive integer, got NULL' . PHP_EOL,
            ],
            '1_invalid_settings' => [
                [0, '1', 2],
                'Invalid settings for Place' . PHP_EOL .
                'Excepted timeout to be a float, got string (1)' . PHP_EOL,
            ],
            'all_valid_settings' => [
                [0, 1.1, 2],
                'Invalid settings for Place' . PHP_EOL,
            ],
        ];
    }
}
