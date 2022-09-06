<?php

namespace App\Tests\Models\Word;

use App\Collections\AggregatedAssociationCollection;
use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\Word;
use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Generic\NumericCollection;

final class WordMetaTest extends TestCase
{
    public function testMetaAggregatedWords(): void
    {
        $word = new Word();

        $ids = NumericCollection::collect(1, 2, 3);

        // write
        $word->setMetaAggregatedWords($ids);

        // read
        $this->assertEquals(
            $ids->toArray(),
            $word->aggregatedWordIds()->toArray()
        );

        // serialize
        $serialized = $word->encodeMeta();

        // deserialize
        $word2 = new Word();
        $word2->meta = $serialized;

        $this->assertEquals(
            $ids->toArray(),
            $word2->aggregatedWordIds()->toArray()
        );
    }

    public function testMetaAggregatedAssociations(): void
    {
        $expected = [[
            2, 1, false, 'some text'
        ]];

        $word = new Word(['id' => 1]);

        $aggregatedAssociation = new AggregatedAssociation(
            new Association(['id' => 2]),
            $word
        );

        $aggregatedAssociation
            ->withJunky(false)
            ->addToLog('some text');

        $aggregatedAssociations = AggregatedAssociationCollection::collect(
            $aggregatedAssociation
        );

        // write
        $word->setMetaAggregatedAssociations($aggregatedAssociations);

        // read
        $this->assertEquals(
            $expected,
            $word->aggregatedAssociationsData()->toArray()
        );

        // serialize
        $serialized = $word->encodeMeta();

        // deserialize
        $word2 = new Word();
        $word2->meta = $serialized;

        $this->assertEquals(
            $expected,
            $word2->aggregatedAssociationsData()->toArray()
        );
    }
}
