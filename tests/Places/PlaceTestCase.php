<?php

namespace Tests\Resiliency\Places;

use PHPUnit\Framework\TestCase;

/**
 * Helper to share fixtures accross Places tests.
 */
class PlaceTestCase extends TestCase
{
    /**
     * @return array
     */
    public function getFixtures()
    {
        return [
            '0_0_0' => [0, 0, 0],
            '1_100_0' => [1, 100, 0],
        ];
    }

    /**
     * @return array
     */
    public function getInvalidFixtures()
    {
        return [
            'minus1_null_false' => [-1, null, false],
        ];
    }
}
