<?php

namespace App\Models;

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
    use CreatedAt;

    private Game $game;
    private Word $word;
    private ?User $user = null;
    private ?Association $association = null;
    private ?self $prev = null;

    private bool $userInitialized = false;
    private bool $associationInitialized = false;
    private bool $prevInitialized = false;

    public function game() : Game
    {
        return $this->game;
    }

    public function withGame(Game $game) : self
    {
        $this->game = $game;
        return $this;
    }

    public function word() : Word
    {
        return $this->word;
    }

    public function withWord(Word $word) : self
    {
        $this->word = $word;
        return $this;
    }
    
    public function user() : ?User
    {
        return $this->user;
    }

    public function withUser(?User $user) : self
    {
        $this->user = $user;
        $this->userInitialized = true;

        return $this;
    }

    public function isBy(User $user) : bool
    {
        return $this->user->equals($user);
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
        Assert::true($this->userInitialized);

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
