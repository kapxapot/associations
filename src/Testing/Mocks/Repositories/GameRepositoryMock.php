<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\GameCollection;
use App\Models\Game;
use App\Models\Language;
use App\Models\User;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Search\SearchParams;
use Plasticode\Search\SearchResult;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Util\Sort;

class GameRepositoryMock extends RepositoryMock implements GameRepositoryInterface
{
    /** @var HydratorInterface|ObjectProxy */
    private $hydrator;

    private GameCollection $games;

    /**
     * @param HydratorInterface|ObjectProxy $hydrator
     */
    public function __construct(
        $hydrator
    )
    {
        $this->hydrator = $hydrator;

        $this->games = GameCollection::empty();
    }

    public function get(?int $id) : ?Game
    {
        return $this->games->first('id', $id);
    }

    public function getAllByLanguage(Language $language) : GameCollection
    {
        return $this
            ->games
            ->where(
                fn (Game $g) => $g->languageId == $language->getId()
            );
    }

    public function getCountByLanguage(Language $language): int
    {
        return $this
            ->getAllByLanguage($language)
            ->count();
    }

    public function save(Game $game) : Game
    {
        if ($this->games->contains($game)) {
            return $this->hydrator->hydrate($game);
        }

        if (!$game->isPersisted()) {
            $game->id = $this->games->nextId();
        }

        $this->games = $this->games->add($game);

        return $this->hydrator->hydrate($game);
    }

    public function store(array $data) : Game
    {
        $game = Game::create($data);

        return $this->save($game);
    }

    public function getCurrentByUser(User $user) : ?Game
    {
        return $this
            ->games
            ->desc(
                fn (Game $g) => $g->createdAt,
                Sort::DATE
            )
            ->first(
                fn (Game $g) => $g->userId == $user->getId() && !$g->isFinished()
            );
    }

    public function getLastByUser(User $user) : ?Game
    {
        return $this
            ->games
            ->desc(
                fn (Game $g) => $g->createdAt,
                Sort::DATE
            )
            ->first(
                fn (Game $g) => $g->userId == $user->getId()
            );
    }

    public function getSearchResult(SearchParams $searchParams): SearchResult
    {
        // placeholder
        return new SearchResult(
            $this->games,
            $this->games->count(),
            $this->games->count()
        );
    }
}
