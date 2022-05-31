<?php

namespace App\Tests\Services;

use App\Services\CasesService;
use PHPUnit\Framework\TestCase;
use Plasticode\Util\Cases;

final class CasesServiceTest extends TestCase
{
    /**
     * @dataProvider invisibleAssociationCountProvider
     */
    public function testInvisibleAssociationCountStr(int $count, ?string $expected): void
    {
        $service = new CasesService(
            new Cases()
        );

        $this->assertEquals($expected, $service->invisibleAssociationCountStr($count));
    }

    public function invisibleAssociationCountProvider(): array
    {
        return [
            [0, null],
            [1, '1 ассоциация скрыта.'],
            [2, '2 ассоциации скрыто.'],
            [105, '105 ассоциаций скрыто.'],
        ];
    }
}
