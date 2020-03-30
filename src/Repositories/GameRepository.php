<?php

namespace App\Repositories;

use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class GameRepository extends IdiormRepository implements GameRepositoryInterface
{
    protected string $entityClass = Game::class;

    public function get(?int $id): ?Game
    {
        return $this->getEntity($id);
    }
}
