<?php

namespace App\Tests\Models\Word;

use App\Models\WordOverride;
use App\Semantics\PartOfSpeech;
use PHPUnit\Framework\TestCase;

final class WordOverrideTest extends TestCase
{
    public function testPartsOfSpeech(): void
    {
        $wo = new WordOverride([
            'pos_correction' => 'noun,verb,adjective',
        ]);

        $parts = $wo->partsOfSpeech();

        $this->assertCount(3, $parts);
        $this->assertEquals(PartOfSpeech::NOUN, $parts[0]->name());
        $this->assertEquals(PartOfSpeech::VERB, $parts[1]->name());
        $this->assertEquals(PartOfSpeech::ADJECTIVE, $parts[2]->name());
    }
}
