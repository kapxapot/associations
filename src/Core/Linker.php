<?php

namespace App\Core;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Association;
use App\Models\Game;
use App\Models\Word;
use Plasticode\Core\Linker as BaseLinker;
use Plasticode\Models\Generic\DbModel;

class Linker extends BaseLinker implements LinkerInterface
{
    private function check(DbModel $model): bool
    {
        return $model !== null && $model->isPersisted();
    }

    public function association(Association $association): ?string
    {
        if (!$this->check($association)) {
            return null;
        }

        return $this->router->pathFor(
            'main.association',
            ['id' => $association->getId()]
        );
    }

    public function game(Game $game): ?string
    {
        if (!$this->check($game)) {
            return null;
        }

        return $this->router->pathFor('main.game', ['id' => $game->getId()]);
    }

    public function word(Word $word): ?string
    {
        if (!$this->check($word)) {
            return null;
        }

        return $this->router->pathFor('main.word', ['id' => $word->getId()]);
    }
}
