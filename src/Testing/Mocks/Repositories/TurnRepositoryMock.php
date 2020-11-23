<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\TurnCollection;
use App\Models\Association;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\TurnRepositoryInterface;

class TurnRepositoryMock implements TurnRepositoryInterface
{
    /** @var HydratorInterface|ObjectProxy */
    private $hydrator;

    private TurnCollection $turns;

    /**
     * @param HydratorInterface|ObjectProxy $hydrator
     */
    public function __construct(
        $hydrator
    )
    {
        $this->hydrator = $hydrator;

        $this->turns = TurnCollection::empty();
    }

    public function get(?int $id) : ?Turn
    {
        return $this->turns->first(
            fn (Turn $t) => $t->getId() == $id
        );
    }

    public function save(Turn $turn) : Turn
    {
        if (!$this->turns->contains($turn)) {
            if (!$turn->isPersisted()) {
                $turn->id = $this->turns->nextId();
            }

            $this->turns = $this->turns->add($turn);
        }

        return $this->hydrator->hydrate($turn);
    }

    public function getAllByGame(Game $game) : TurnCollection
    {
        return $this
            ->turns
            ->where(
                fn (Turn $t) => $t->gameId == $game->getId()
            );
    }

    public function getAllByAssociation(Association $association) : TurnCollection
    {
        return $this
            ->turns
            ->where(
                fn (Turn $t) => $t->associationId == $association->getId()
            );
    }

    public function getAllByLanguage(Language $language) : TurnCollection
    {
        return $this
            ->turns
            ->where(
                fn (Turn $t) => $t->languageId == $language->getId()
            );
    }

    public function getCountByLanguage(Language $language): int
    {
        return $this
            ->getAllByLanguage($language)
            ->count();
    }

    public function getAllByUser(User $user, ?Language $language = null) : TurnCollection
    {
        $turns = $language
            ? $this->getAllByLanguage($language)
            : $this->turns;

        return $turns->where(
            fn (Turn $t) => $t->userId == $user->getId()
        );
    }

    public function getAllByWord(Word $word) : TurnCollection
    {
        return $this
            ->turns
            ->where(
                fn (Turn $t) => $t->wordId == $word->getId()
            );
    }
}
