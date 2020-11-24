<?php

namespace App\Models;

use App\Collections\TurnCollection;
use App\Collections\UserCollection;
use App\Collections\WordCollection;
use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Util\Date;

/**
 * @property integer $languageId
 * @property integer $userId
 * @property string|null $finishedAt
 * @method Language language()
 * @method TurnCollection turns()
 * @method string url()
 * @method User user()
 * @method static withLanguage(Language|callable $language)
 * @method static withTurns(TurnCollection|callable $turns)
 * @method static withUrl(string|callable $url)
 * @method static withUser(User|callable $user)
 */
class Game extends DbModel
{
    use CreatedAt;

    protected function requiredWiths(): array
    {
        return ['language', 'turns', 'url', 'user'];
    }

    public function lastTurn() : ?Turn
    {
        // turns are sorted backwards, so first
        return $this->turns()->first();
    }

    public function beforeLastTurn() : ?Turn
    {
        return $this->lastTurn()
            ? $this->lastTurn()->prev()
            : null;
    }

    public function words() : WordCollection
    {
        return $this->turns()->words();
    }

    public function lastTurnWord() : ?Word
    {
        return $this->lastTurn()
            ? $this->lastTurn()->word()
            : null;
    }

    public function beforeLastTurnWord() : ?Word
    {
        return $this->beforeLastTurn()
            ? $this->beforeLastTurn()->word()
            : null;
    }

    public function creator() : User
    {
        return $this->user();
    }

    public function isStarted() : bool
    {
        return $this->turns()->any();
    }

    public function isFinished() : bool
    {
        return $this->finishedAt !== null;
    }

    public function isWonByPlayer() : bool
    {
        return
            $this->isFinished()
            && $this->lastTurn()
            && $this->lastTurn()->isPlayerTurn();
    }

    public function isWonByAi() : bool
    {
        return
            $this->isFinished()
            && $this->lastTurn()
            && $this->lastTurn()->isAiTurn();
    }

    public function players() : UserCollection
    {
        return $this->turns()->users();
    }

    public function hasPlayer(User $user) : bool
    {
        return $this
            ->players()
            ->ids()
            ->contains($user->getId());
    }

    public function containsWord(Word $word) : bool
    {
        return $this
            ->words()
            ->any('id', $word->getId());
    }

    public function displayName() : string
    {
        return 'Игра #' . $this->getId();
    }

    public function finishedAtIso() : string
    {
        return Date::iso($this->finishedAt);
    }
}
