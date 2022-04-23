<?php

namespace App\Tests\Models;

use App\Collections\WordCollection;
use App\Collections\WordRelationCollection;
use App\Models\Word;
use App\Models\WordRelation;
use App\Models\WordRelationType;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Testing\Factories\WordRepositoryFactory;
use PHPUnit\Framework\TestCase;

class WordAggregatedWordsTest extends TestCase
{
    private WordRepositoryInterface $wordRepository;

    private WordRelationType $relationType;

    public function setUp(): void
    {
        parent::setUp();

        $this->wordRepository = WordRepositoryFactory::make();

        $this->relationType = new WordRelationType();
        $this->relationType->sharingAssociationsDown = 1;
    }

    public function tearDown(): void
    {
        unset($this->relationType);
        unset($this->wordRepository);

        parent::tearDown();
    }

    public function testAggregate(): void
    {
        // Main word tree:
        //
        //               $w5
        //                 \
        //                  v
        // $w1 -> ($w2) -> $w3 -> $w4
        //          ^
        //         /
        //       $w6

        $w1 = $this->addWord('w1');
        $w2 = $this->addWord('w2');
        $w3 = $this->addWord('w3');
        $w4 = $this->addWord('w4');
        $w5 = $this->addWord('w5');
        $w6 = $this->addWord('w6');

        $this->addMain($w1, $w2);
        $this->addMain($w2, $w3);
        $this->addMain($w3, $w4);
        $this->addMain($w5, $w3);
        $this->addMain($w6, $w2);

        $noDependents = WordCollection::empty();

        $w1->withDependents($noDependents);

        $w2->withDependents(
            WordCollection::collect($w1, $w6)
        );

        $w3->withDependents(
            WordCollection::collect($w2, $w5)
        );

        $w4->withDependents(
            WordCollection::collect($w3)
        );

        $w5->withDependents($noDependents);

        $w6->withDependents($noDependents);

        // assert
        $aggregatedWords = $w2->aggregatedWords();

        $this->assertCount(6, $aggregatedWords);

        $this->assertTrue($aggregatedWords->contains($w1));
        $this->assertTrue($aggregatedWords->contains($w2));
        $this->assertTrue($aggregatedWords->contains($w3));
        $this->assertTrue($aggregatedWords->contains($w4));
        $this->assertTrue($aggregatedWords->contains($w5));
        $this->assertTrue($aggregatedWords->contains($w6));
    }

    private function addMain(Word $first, ?Word $second): Word
    {
        $relations = WordRelationCollection::make();

        if ($second !== null) {
            $relation = (new WordRelation([
                'word_id' => $first->getId(),
                'main_word_id' => $second->getId(),
                'primary' => 1,
            ]))
                ->withType($this->relationType)
                ->withWord($first)
                ->withMainWord($second);

            $relations = $relations->add($relation);
        }

        return $first
            ->withMain($second)
            ->withRelations($relations);
    }

    private function addWord(string $word): Word
    {
        $word = $this->wordRepository->save(
            new Word(['word' => $word])
        );

        return $this->addMain($word, null);
    }
}
