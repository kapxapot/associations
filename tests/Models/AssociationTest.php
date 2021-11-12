<?php

namespace App\Tests\Models;

use App\Models\Association;
use PHPUnit\Framework\TestCase;

final class AssociationTest extends TestCase
{
    public function testKey(): void
    {
        $a = new Association(['first_word_id' => 1, 'second_word_id' => 2]);

        $this->assertEquals('1:2', $a->key());
    }
}
