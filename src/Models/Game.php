<?php

namespace App\Models;

use App\Collections\TurnCollection;
use App\Collections\UserCollection;
use App\Collections\WordCollection;
use App\Models\Traits\WithLanguage;
use App\Models\Traits\WithUser;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\WithUrl;
use Plasticode\Util\Date;
use Webmozart\Assert\Assert;

/**
 * @property string|null $finishedAt
 */
class Game extends DbModel
{
    use CreatedAt;
    use WithLanguage;
    use WithUrl;
    use WithUser;

    protected TurnCollection $turns;

    private bool $turnsInitialized = false;

    /**
     * Sorted backwards.
     */
    public function turns() : TurnCollection
    {
        Assert::true($this->turnsInitialized);

        return $this->turns;
    }

    public function withTurns(TurnCollection $turns) : self
    {
        $this->turns = $turns;
        $this->turnsInitialized = true;

        return $this;
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
