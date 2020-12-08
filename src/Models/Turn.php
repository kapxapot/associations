<?php

namespace App\Models;

use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;

/**
 * @property integer $gameId
 * @property integer $languageId
 * @property integer $wordId
 * @property integer|null $userId
 * @property integer|null $associationId
 * @property integer|null $prevTurnId
 * @method Association|null association()
 * @method Game game()
 * @method static|null prev()
 * @method User|null user()
 * @method Word word()
 * @method static withAssociation(Association|callable|null $association)
 * @method static withGame(Game|callable $game)
 * @method static withPrev(static|callable|null $prev)
 * @method static withUser(User|callable|null $user)
 * @method static withWord(Word|callable $word)
 */
class Turn extends DbModel implements CreatedAtInterface
{
    use CreatedAt;

    protected function requiredWiths() : array
    {
        return ['association', 'game', 'prev', 'user', 'word'];
    }

    public function isBy(?User $user) : bool
    {
        return $this->user()
            ? $this->user()->equals($user)
            : is_null($user);
    }

    public function isPlayerTurn() : bool
    {
        return !is_null($this->user());
    }

    public function isAiTurn() : bool
    {
        return !$this->isPlayerTurn();
    }

    public function isFinished() : bool
    {
        return !is_null($this->finishedAt);
    }
}
