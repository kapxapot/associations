<?php

namespace App\Tests\Models;

use App\Collections\TurnCollection;
use App\Collections\WordRelationCollection;
use App\Exceptions\RecentRelatedWordException;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\Word;
use App\Models\WordRelation;
use App\Models\WordRelationType;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\AssociationService;
use App\Services\TurnService;
use App\Testing\Mocks\Repositories\WordRepositoryMock;
use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;
use Prophecy\PhpUnit\ProphecyTrait;

final class WordRemotelyRelatedTest extends TestCase
{
    use ProphecyTrait;

    private WordRepositoryInterface $wordRepository;

    private Language $language;

    private Word $childMain;
    private Word $child;
    private Word $children;
    private Word $happiness;

    public function setUp(): void
    {
        parent::setUp();

        $this->language = new Language(['id' => 1]);

        // [1] ребёнок
        // [2] ребенок => ребёнок      <= these words must be
        // [3] дети -> ребёнок         <= remotely related to each other

        $this->childMain = (new Word([
            'language_id' => $this->language->getId(),
            'word' => 'ребёнок',
        ]))
            ->withLanguage($this->language)
            ->withMain(null);

        $this->child = (new Word([
            'language_id' => $this->language->getId(),
            'word' => 'ребенок',
        ]))
            ->withLanguage($this->language)
            ->withMain($this->childMain);

        $this->children = (new Word([
            'language_id' => $this->language->getId(),
            'word' => 'дети',
        ]))
            ->withLanguage($this->language)
            ->withMain(null);

        $this->happiness = (new Word([
            'language_id' => $this->language->getId(),
            'word' => 'счастье',
        ]))
            ->withLanguage($this->language)
            ->withMain(null);

        $altRelationType = new WordRelationType([
            'id' => 1,
            'name' => 'Alternative form',
            'tag' => 'ALT',
        ]);

        $pluRelationType = new WordRelationType([
            'id' => 2,
            'name' => 'Plural form',
            'tag' => 'PLU',
        ]);

        $relationChildChildMain = (new WordRelation([
            'id' => 1,
            'word_id' => $this->child->getId(),
            'main_word_id' => $this->childMain->getId(),
            'primary' => 1,
        ]))
            ->withType($altRelationType)
            ->withWord($this->child)
            ->withMainWord($this->childMain);

        $relationChildrenChildMain = (new WordRelation([
            'id' => 2,
            'word_id' => $this->children->getId(),
            'main_word_id' => $this->childMain->getId(),
            'primary' => 0,
        ]))
            ->withType($pluRelationType)
            ->withWord($this->children)
            ->withMainWord($this->childMain);

        $noRelations = WordRelationCollection::empty();

        $this->child
            ->withRelations(
                WordRelationCollection::collect($relationChildChildMain)
            )
            ->withCounterRelations($noRelations);

        $this->children
            ->withRelations(
                WordRelationCollection::collect($relationChildrenChildMain)
            )
            ->withCounterRelations($noRelations);

        $this->childMain
            ->withRelations($noRelations)
            ->withCounterRelations(
                WordRelationCollection::collect(
                    $relationChildChildMain,
                    $relationChildrenChildMain
                )
            );

        $this->happiness
            ->withRelations($noRelations)
            ->withCounterRelations($noRelations);

        $this->wordRepository = new WordRepositoryMock();

        $this->wordRepository->save($this->childMain);
        $this->wordRepository->save($this->child);
        $this->wordRepository->save($this->children);
        $this->wordRepository->save($this->happiness);
    }

    public function tearDown(): void
    {
        unset($this->wordRepository);

        unset($this->happiness);
        unset($this->children);
        unset($this->child);
        unset($this->childMain);

        unset($this->language);

        parent::tearDown();
    }

    public function testIsRemotelyRelatedTo(): void
    {
        $this->assertTrue($this->child->isRemotelyRelatedTo($this->children));
        $this->assertTrue($this->children->isRemotelyRelatedTo($this->child));
    }

    public function testRemotelyRelatedTurnFails(): void
    {
        $game = new Game(['language_id' => $this->language->getId()]);

        $game->withTurns(
            TurnCollection::collect(
                (new Turn())->withWord($this->child),
                (new Turn())->withWord($this->happiness)
            )
        );

        $game->withLanguage($this->language);

        $turnService = new TurnService(
            $this->prophesize(GameRepositoryInterface::class)->reveal(),
            $this->prophesize(TurnRepositoryInterface::class)->reveal(),
            $this->wordRepository,
            $this->prophesize(AssociationService::class)->reveal(),
            $this->prophesize(EventDispatcher::class)->reveal()
        );

        $this->expectException(RecentRelatedWordException::class);

        $turnService->validatePlayerTurn($game, $this->children->word);
    }
}
