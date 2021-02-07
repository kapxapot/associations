<?php

namespace App\Tests\Parsing;

use App\External\DictionaryApi;
use App\Models\Definition;
use App\Parsing\DefinitionParser;
use App\Semantics\Definition\DefinitionAggregate;
use PHPUnit\Framework\TestCase;
use Plasticode\IO\File;

final class DefinitionParserTest extends TestCase
{
    public function testParseDictionaryApi(): void
    {
        $path = File::combine(__DIR__, 'undead.json');
        $jsonData = File::load($path);
        
        $definition = new Definition(
            [
                'source' => DictionaryApi::SOURCE,
                'json_data' => $jsonData,
            ]
        );

        $parsed = (new DefinitionParser())->parse($definition);

        $this->assertInstanceOf(DefinitionAggregate::class, $parsed);

        $entries = $parsed->entries();

        $this->assertCount(2, $entries);

        $this->assertEquals(
            [
                'Содержать в неге (в 1 знач.) холить, баловать.',
                'Приводить в состояние неги (во 2 знач.).',
            ],
            $entries[0]->definitions()->toArray()
        );

        $this->assertEquals(
            [
                'В русской мифологии: фантастические существа (лешие, ведьмы, русалки и т. п.).',
            ],
            $entries[1]->definitions()->toArray()
        );
    }
}
