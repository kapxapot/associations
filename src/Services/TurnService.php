<?php

namespace App\Services;

use App\Collections\TurnCollection;
use App\Events\Turn\TurnCreatedEvent;
use App\Exceptions\DuplicateWordException;
use App\Exceptions\RecentRelatedWordException;
use App\Exceptions\StronglyRelatedWordException;
use App\Exceptions\TurnException;
use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\DTO\PseudoTurn;
use App\Models\Game;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Exception;
use Plasticode\Events\EventDispatcher;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Date;
use Webmozart\Assert\Assert;

class TurnService
{
    use LoggerAwareTrait;

    private GameRepositoryInterface $gameRepository;
    private TurnRepositoryInterface $turnRepository;
    private WordRepositoryInterface $wordRepository;

    private AssociationService $associationService;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        TurnRepositoryInterface $turnRepository,
        WordRepositoryInterface $wordRepository,
        AssociationService $associationService,
        EventDispatcher $eventDispatcher
    )
    {
        $this->gameRepository = $gameRepository;
        $this->turnRepository = $turnRepository;
        $this->wordRepository = $wordRepository;
        $this->associationService = $associationService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns true on success.
     */
    public function finishTurn(Turn $turn, ?string $finishDate = null): bool
    {
        if ($turn->isFinished()) {
            return true;
        }

        $turn->finishedAt = $finishDate ?? Date::dbNow();

        $this->turnRepository->save($turn);

        return true;
    }

    /**
     * Returns new player turn and AI turn/answer if it happens.
     */
    public function newPlayerTurn(
        User $user,
        Game $game,
        Word $word,
        ?string $originalUtterance = null
    ): TurnCollection
    {
        $turn = $this->newTurn($game, $word, null, $user, $originalUtterance);

        $event = new TurnCreatedEvent($turn);
        $this->eventDispatcher->dispatch($event);

        $turns = TurnCollection::collect($turn);

        $aiTurn = $this->processPlayerTurn($turn);

        if ($aiTurn) {
            $turns = $turns->add($aiTurn);
        }

        return $turns;
    }

    public function newAiTurn(Game $game, Word $word, ?Association $association = null): Turn
    {
        $turn = $this->newTurn($game, $word, $association);

        $this->processAiTurn($turn);

        return $turn;
    }

    private function newTurn(
        Game $game,
        Word $word,
        ?Association $association = null,
        ?User $user = null,
        ?string $originalUtterance = null
    ): Turn
    {
        $language = $game->language();

        $turn = Turn::create();

        $turn->gameId = $game->getId();
        $turn->languageId = $language->getId();
        $turn->originalUtterance = $originalUtterance;

        if ($user) {
            $turn->userId = $user->getId();
        }

        $turn->wordId = $word->getId();
        $prevTurn = $game->lastTurn();

        if ($prevTurn) {
            $turn->prevTurnId = $prevTurn->getId();

            $prevWord = $prevTurn->word();

            $association ??= $this->associationService->getOrCreate(
                $prevWord,
                $word,
                $user,
                $language
            );

            if ($association->isReal()) {
                $turn->associationId = $association->toReal()->getId();
            }
        }

        $turn = $this->turnRepository->save($turn);

        // todo: this relation must be updated by repositories (+ entity manager)
        $this->gameRepository->save($game);

        return $turn;
    }

    public function processAiTurn(Turn $turn): void
    {
        $this->finishPrevTurn($turn);
    }

    private function finishPrevTurn(Turn $turn): void
    {
        if ($turn->prev()) {
            $this->finishTurn(
                $turn->prev(),
                $turn->createdAt
            );
        }
    }

    /**
     * Returns AI turn in answer to player turn (if any).
     */
    public function processPlayerTurn(Turn $turn): ?Turn
    {
        $this->finishPrevTurn($turn);

        return $this->nextAiTurn($turn);
    }

    private function nextAiTurn(Turn $turn): ?Turn
    {
        $game = $turn->game();

        $pseudoTurn = $this->findAnswer($game, $turn->word(), $turn->user());

        if ($pseudoTurn) {
            $word = $pseudoTurn->word();
            $association = $pseudoTurn->association();

            // if pseudo turn is not null, word must be not null
            Assert::notNull($word);

            return $this->newAiTurn($game, $word, $association);
        }

        $this->finishGame($game);

        return null;
    }

    /**
     * Returns true on success.
     *
     * Todo: this should belong to GameService, but creates a circular dependency
     */
    public function finishGame(Game $game): bool
    {
        if ($game->isFinished()) {
            return false;
        }

        $game->finishedAt = Date::dbNow();

        $this->gameRepository->save($game);

        if ($game->lastTurn()) {
            return $this->finishTurn(
                $game->lastTurn(),
                $game->finishedAt
            );
        }

        return true;
    }

    /**
     * Validates player turn.
     *
     * Normalized word string expected.
     *
     * @throws TurnException
     */
    public function validatePlayerTurn(Game $game, string $wordStr): void
    {
        $language = $game->language();

        $word = $this
            ->wordRepository
            ->findInLanguage($language, $wordStr);

        // unknown yet word
        if ($word === null) {
            return;
        }

        $this->throwIfCantBePlayed($game, $word);
    }

    public function findAnswer(Game $game, Word $word, ?User $user = null): ?PseudoTurn
    {
        // 1. looking in fuzzy public associations
        // 2. looking in private associations

        $selectors = [
            fn (Association $a) => $a->isFuzzyPublic(),
            fn (Association $a) => $a->isPrivate(),
        ];

        foreach ($selectors as $selector) {
            /** @var AggregatedAssociation $answerAssociation */
            $answerAssociation = $word
                ->aggregatedAssociations()
                ->notJunky()
                ->where($selector)
                ->where(
                    fn (Association $a) => $a->isPlayableAgainst($user)
                )
                ->shuffle()
                ->first(
                    fn (AggregatedAssociation $a) => $this->canBePlayed(
                        $game,
                        $a->otherThanAnchor()
                    )
                );

            if ($answerAssociation !== null) {
                return new PseudoTurn(
                    $answerAssociation,
                    $answerAssociation->otherThanAnchor(),
                    $word
                );
            }
        }

        return null;
    }

    /**
     * Exception wrapper for game context checks.
     */
    private function canBePlayed(Game $game, Word $word): bool
    {
        try {
            $this->throwIfCantBePlayed($game, $word);
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Game context checks.
     *
     * @throws TurnException
     */
    private function throwIfCantBePlayed(Game $game, Word $word): void
    {
        // check for the same word
        if ($game->containsWord($word)) {
            throw new DuplicateWordException($word->word);
        }

        // check for a canonical equal word
        $stronglyRelatedWord = $game->getCanonicalEqualWordFor($word);

        if ($stronglyRelatedWord !== null) {
            throw new StronglyRelatedWordException($stronglyRelatedWord->word);
        }

        // check for a recent related/remotely related word
        $recentRelatedWord = $game->getRecentRelatedWordFor($word);

        if ($recentRelatedWord !== null) {
            throw new RecentRelatedWordException($recentRelatedWord->word);
        }
    }
}
