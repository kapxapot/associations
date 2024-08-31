<?php

namespace App\Core;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Association;
use App\Models\Game;
use App\Models\Word;
use Brightwood\Models\Stories\Core\JsonStory;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Core\Linker as BaseLinker;
use Plasticode\Models\Generic\DbModel;

class Linker extends BaseLinker implements LinkerInterface
{
    private function checkPersisted(DbModel $model): bool
    {
        return $model && $model->isPersisted();
    }

    public function association(Association $association): ?string
    {
        if (!$this->checkPersisted($association)) {
            return null;
        }

        return $this->router->pathFor(
            'main.association',
            ['id' => $association->getId()]
        );
    }

    public function game(Game $game): ?string
    {
        if (!$this->checkPersisted($game)) {
            return null;
        }

        return $this->router->pathFor('main.game', ['id' => $game->getId()]);
    }

    public function word(Word $word): ?string
    {
        if (!$this->checkPersisted($word)) {
            return null;
        }

        return $this->router->pathFor('main.word', ['id' => $word->getId()]);
    }

    public function story(Story $story): ?string
    {
        if (!$this->checkPersisted($story) || !$story->isEditable()) {
            return null;
        }

        return $this->router->pathFor('api.stories.get', ['uuid' => $story->uuid]);
    }
}
