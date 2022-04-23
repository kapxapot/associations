<?php

namespace App\Tests\Semantics;

use App\Collections\AssociationCollection;
use App\Collections\AssociationFeedbackCollection;
use App\Collections\AssociationOverrideCollection;
use App\Collections\WordCollection;
use App\Collections\WordRelationCollection;
use App\Models\Association;
use App\Models\Word;
use App\Models\WordRelation;
use App\Models\WordRelationType;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Association\AssociationCongregator;
use App\Semantics\Association\CachingAssociationAggregator;
use App\Semantics\Association\NaiveAssociationAggregator;
use App\Semantics\Interfaces\AssociationAggregatorInterface;
use App\Semantics\Scope;
use App\Testing\Factories\WordRepositoryFactory;
use App\Testing\Mocks\Repositories\AssociationRepositoryMock;
use PHPUnit\Framework\TestCase;

class AssociationAggregatorTest extends TestCase
{
    private AssociationRepositoryInterface $associationRepository;
    private WordRepositoryInterface $wordRepository;

    private WordRelationType $relationType;

    // aggregators
    private NaiveAssociationAggregator $naiveAggregator;
    private CachingAssociationAggregator $cachingAggregator;

    public function setUp(): void
    {
        parent::setUp();

        $this->associationRepository = new AssociationRepositoryMock();
        $this->wordRepository = WordRepositoryFactory::make();

        $this->relationType = new WordRelationType();
        $this->relationType->sharingAssociationsDown = 1;

        // aggregators
        $congregator = new AssociationCongregator();

        $this->naiveAggregator = new NaiveAssociationAggregator($congregator);

        $this->cachingAggregator = new CachingAssociationAggregator(
            $this->associationRepository,
            $this->wordRepository,
            $congregator
        );
    }

    public function tearDown(): void
    {
        unset($this->cachingAggregator);
        unset($this->naiveAggregator);

        unset($this->relationType);
        unset($this->wordRepository);
        unset($this->associationRepository);

        parent::tearDown();
    }

    /**
     * @dataProvider aggregateProvider
     */
    public function testAggregate(string $aggregatorClass): void
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
        //
        // Associations:
        //
        // $w1 -> $w11
        // $w2 -> $w12
        // $w3 -> $w13
        // $w4 -> $w14
        // $w5 -> $w15
        // $w6 -> $w16

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

        // associated words
        $w11 = $this->addWord('w11');
        $w12 = $this->addWord('w12');
        $w13 = $this->addWord('w13');
        $w14 = $this->addWord('w14');
        $w15 = $this->addWord('w15');
        $w16 = $this->addWord('w16');

        // associations
        $this->associate($w1, $w11);
        $this->associate($w2, $w12);
        $this->associate($w3, $w13);
        $this->associate($w4, $w14);
        $this->associate($w5, $w15);
        $this->associate($w6, $w16);

        // assert
        $aggregator = $this->makeAggregator($aggregatorClass);
        $aggregated = $aggregator->aggregateFor($w2);

        $this->assertCount(6, $aggregated);
    }

    public function aggregateProvider(): array
    {
        return [
            'naive' => [NaiveAssociationAggregator::class],
            'caching' => [CachingAssociationAggregator::class],
        ];
    }

    private function makeAggregator(string $aggregatorClass): ?AssociationAggregatorInterface
    {
        switch ($aggregatorClass) {
            case NaiveAssociationAggregator::class:
                return $this->naiveAggregator;

            case CachingAssociationAggregator::class:
                return $this->cachingAggregator;
        }

        return null;
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

    private function associate(Word $first, Word $second): Word
    {
        $association = (new Association([
            'first_word_id' => $first->getId(),
            'second_word_id' => $second->getId(),
            'scope' => Scope::PRIVATE,
        ]))
            ->withUrl(null)
            ->withCreator(null)
            ->withLanguage(null)
            ->withMe(null)
            ->withTurns(null)
            ->withFeedbacks(AssociationFeedbackCollection::empty())
            ->withFirstWord($first)
            ->withOverrides(AssociationOverrideCollection::empty())
            ->withSecondWord($second);

        $association->withCanonical($association);

        $association = $this->associationRepository->save($association);

        return $first->withAssociations(
            AssociationCollection::collect($association)
        );
    }

    private function addWord(string $word): Word
    {
        $word = $this->wordRepository->save(
            new Word(['word' => $word])
        );

        return $this->addMain($word, null);
    }
}
