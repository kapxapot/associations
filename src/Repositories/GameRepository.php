<?php

namespace App\Repositories;

use App\Collections\GameCollection;
use App\Models\Game;
use App\Models\Language;
use App\Models\User;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Traits\ByUserRepository;
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class GameRepository extends IdiormRepository implements GameRepositoryInterface
{
    use ByUserRepository;
    use WithLanguageRepository;

    /**
     * @inheritDoc
     */
    protected function entityClass() : string
    {
        return Game::class;
    }

    public function get(?int $id) : ?Game
    {
        return $this->getEntity($id);
    }

    public function getAllByLanguage(Language $language) : GameCollection
    {
        return GameCollection::from(
            $this->getByLanguageQuery($language)
        );
    }

    public function save(Game $game) : Game
    {
        return $this->saveEntity($game);
    }

    public function store(array $data) : Game
    {
        return $this->storeEntity($data);
    }

    public function getCurrentByUser(User $user) : ?Game
    {
        return $this
            ->getAllByUserQuery($user)
            ->whereNull('finished_at')
            ->orderByDesc('id')
            ->one();
    }

    public function getLastByUser(User $user) : ?Game
    {
        return $this
            ->getAllByUserQuery($user)
            ->orderByDesc('id')
            ->one();
    }

    // queries

    protected function getAllByUserQuery(User $user) : Query
    {
        return $this->filterByUser(
            $this->query(),
            $user
        );
    }
}
