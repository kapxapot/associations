<?php

namespace App\Tests\Models\DTO;

use App\Models\DTO\EtherealAssociation;
use App\Models\Word;
use PHPUnit\Framework\TestCase;

final class EtherealAssociationTest extends TestCase
{
    public function testKey(): void
    {
        $a = new EtherealAssociation(
            new Word(['id' => 1]),
            new Word(['id' => 2])
        );

        $this->assertEquals('1:2', $a->key());
    }
}
