<?php

namespace App\Repositories;

use App\Models\Game;
use App\Models\User;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Traits\ByUserRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class GameRepository extends IdiormRepository implements GameRepositoryInterface
{
    use ByUserRepository;

    protected string $entityClass = Game::class;

    public function get(?int $id) : ?Game
    {
        return $this->getEntity($id);
    }

    public function store(array $data) : Game
    {
        return $this->storeEntity($data);
    }

    protected function getAllByUserQuery(User $user) : Query
    {
        return $this->filterByUser(
            $this->query(),
            $user
        );
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
}
