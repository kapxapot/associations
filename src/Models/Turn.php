<?php

namespace App\Models;

use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;

/**
 * @property integer|null $associationId
 * @property integer $gameId
 * @property integer $languageId
 * @property string|null $originalUtterance
 * @property integer|null $prevTurnId
 * @property integer|null $userId
 * @property integer $wordId
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

    protected function requiredWiths(): array
    {
        return ['association', 'game', 'prev', 'user', 'word'];
    }

    public function isBy(?User $user): bool
    {
        return $this->user()
            ? $this->user()->equals($user)
            : is_null($user);
    }

    public function isPlayerTurn(): bool
    {
        return $this->user() !== null;
    }

    public function isAiTurn(): bool
    {
        return !$this->isPlayerTurn();
    }

    public function isFinished(): bool
    {
        return $this->finishedAt !== null;
    }

    public function isNative(): bool
    {
        $prevTurn = $this->prev();

        if ($prevTurn === null) {
            return true;
        }

        $prevWord = $prevTurn->word();

        return $this->association()->hasWord($prevWord);
    }
}
