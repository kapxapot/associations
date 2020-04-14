<?php

namespace App\Tests\Services;

use App\Services\AnniversaryService;
use PHPUnit\Framework\TestCase;

final class AnniversaryServiceTest extends TestCase
{
    /**
     * @dataProvider isAnniversaryProvider
     */
    public function testIsAnniversary(int $num, bool $expected) : void
    {
        $service = new AnniversaryService();

        $this->assertEquals(
            $expected,
            $service->isAnniversary($num)
        );
    }

    public function isAnniversaryProvider() : array
    {
        return [
            [0, false],
            [100, false],
            [999, false],
            [1000, true],
            [1199, true],
            [1200, false],
            [2000, true],
            [2199, true],
            [2200, false],
            [10000, true],
            [11999, true],
            [12000, false],
            [20000, true],
            [21999, true],
            [22000, false],
        ];
    }

    /**
     * @dataProvider toAnniversaryNumberProvider
     */
    public function testToAnniversaryNumber(int $num, int $expected) : void
    {
        $service = new AnniversaryService();

        $this->assertEquals(
            $expected,
            $service->toAnniversaryNumber($num)
        );
    }

    public function toAnniversaryNumberProvider() : array
    {
        return [
            [0, 0],
            [100, 100],
            [1000, 1000],
            [1199, 1000],
            [2000, 2000],
            [2199, 2000],
            [10000, 10000],
            [11999, 10000],
            [20000, 20000],
            [21999, 20000],
        ];
    }

    /**
     * @dataProvider toAnniversaryProvider
     */
    public function testToAnniversary(int $num, ?int $expected) : void
    {
        $service = new AnniversaryService();

        $this->assertEquals(
            $expected,
            $service->toAnniversary($num)
        );
    }

    public function toAnniversaryProvider() : array
    {
        return [
            [0, null],
            [100, null],
            [999, null],
            [1000, 1000],
            [1199, 1000],
            [1200, null],
            [2000, 2000],
            [2199, 2000],
            [2200, null],
            [10000, 10000],
            [11999, 10000],
            [12000, null],
            [20000, 20000],
            [21999, 20000],
            [22000, null],
        ];
    }
}
