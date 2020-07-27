<?php

namespace Tests\Resiliency\Utils;

use PHPUnit\Framework\TestCase;
use Resiliency\Utils\Assert;

class AssertTest extends TestCase
{
    /**
     * @dataProvider getValues
     *
     * @param mixed $value
     * @param bool $expected
     */
    public function testIsPositiveValue($value, $expected): void
    {
        self::assertSame($expected, Assert::isPositiveValue($value));
    }

    /**
     * @dataProvider getURIs
     *
     * @param mixed $value
     * @param bool $expected
     */
    public function testIsURI($value, $expected): void
    {
        self::assertSame($expected, Assert::isURI($value));
    }

    /**
     * @dataProvider getStrings
     *
     * @param mixed $value
     * @param bool $expected
     */
    public function testIsString($value, $expected): void
    {
        self::assertSame($expected, Assert::isString($value));
    }

    public function getValues(): array
    {
        return [
            '0' => [0, true],
            'str_0' => ['0', false],
            'float' => [0.1, true],
            'stdclass' => [new \stdClass(), false],
            'callable' => [
                fn () => 0,
                false,
            ],
            'negative' => [-1, false],
            'bool' => [false, false],
        ];
    }

    public function getURIs(): array
    {
        return [
            'valid' => ['http://www.prestashop.com', true],
            'int' => [0, false],
            'null' => [null, false],
            'bool' => [false, false],
            'local' => ['http://localhost', true],
            'ssh' => ['ssh://git@git.example.com/FOO/my_project.git', true],
        ];
    }

    public function getStrings(): array
    {
        return [
            'valid' => ['foo', true],
            'empty' => ['', false],
            'null' => [null, false],
            'bool' => [false, false],
            'stdclass' => [new \stdClass(), false],
            'valid2' => ['INVALID_STATE', true],
        ];
    }
}
