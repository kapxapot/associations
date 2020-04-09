<?php

namespace App\Models;

use App\Models\Traits\WithUser;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Webmozart\Assert\Assert;

/**
 * @property integer $gameId
 * @property integer $wordId
 * @property integer|null $userId
 * @property integer|null $associationId
 * @property integer|null $prevTurnId
 */
class Turn extends DbModel
{
    use CreatedAt, WithUser;

    protected Game $game;
    protected Word $word;
    protected ?User $user = null;
    protected ?Association $association = null;
    protected ?self $prev = null;

    private bool $gameInitialized = false;
    private bool $wordInitialized = false;
    private bool $userInitialized = false;
    private bool $associationInitialized = false;
    private bool $prevInitialized = false;

    public function game() : Game
    {
        Assert::true($this->gameInitialized);

        return $this->game;
    }

    public function withGame(Game $game) : self
    {
        $this->game = $game;
        $this->gameInitialized = true;

        return $this;
    }

    public function word() : Word
    {
        Assert::true($this->wordInitialized);

        return $this->word;
    }

    public function withWord(Word $word) : self
    {
        $this->word = $word;
        $this->wordInitialized = true;

        return $this;
    }

    public function isBy(User $user) : bool
    {
        return $this->user()->equals($user);
    }

    public function association() : ?Association
    {
        Assert::true($this->associationInitialized);

        return $this->association;
    }

    public function withAssociation(?Association $association) : self
    {
        $this->association = $association;
        $this->associationInitialized = true;

        return $this;
    }

    public function isPlayerTurn() : bool
    {
        return !is_null($this->user());
    }

    public function isAiTurn() : bool
    {
        return !$this->isPlayerTurn();
    }

    public function prev() : ?Turn
    {
        Assert::true($this->prevInitialized);

        return $this->prev;
    }

    public function withPrev(?self $prev) : self
    {
        $this->prev = $prev;
        $this->prevInitialized = true;

        return $this;
    }

    public function isFinished() : bool
    {
        return !is_null($this->finishedAt);
    }
}
