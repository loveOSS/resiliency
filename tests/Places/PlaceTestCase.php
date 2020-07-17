<?php

namespace Tests\Resiliency\Places;

use PHPUnit\Framework\TestCase;

/**
 * Helper to share fixtures across Places tests.
 */
class PlaceTestCase extends TestCase
{
    public function getFixtures(): array
    {
        return [
            '0_0_0' => [0, 0, 0],
            '1_100_0' => [1, 100, 0],
        ];
    }

    public function getInvalidFixtures(): array
    {
        return [
            'minus1_null_false' => [-1, -1.1, -1.1],
        ];
    }
}
