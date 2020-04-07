<?php

namespace App\Models;

use Plasticode\Models\User as UserBase;
use Plasticode\Util\Date;
use Webmozart\Assert\Assert;

class User extends UserBase
{
    protected bool $mature = false;
    protected ?Game $currentGame = null;
    protected ?Game $lastGame = null;

    private bool $matureInitialized = false;
    private bool $currentGameInitialized = false;
    private bool $lastGameInitialized = false;

    public function isMature() : bool
    {
        Assert::true($this->matureInitialized);

        return $this->mature;
    }

    public function withMature(bool $mature) : self
    {
        $this->mature = $mature;
        $this->matureInitialized = true;

        return $this;
    }

    public function currentGame() : ?Game
    {
        Assert::true($this->currentGameInitialized);

        return $this->currentGame;
    }

    public function withCurrentGame(?Game $game) : self
    {
        $this->currentGame = $game;
        $this->currentGameInitialized = true;

        return $this;
    }

    public function lastGame() : ?Game
    {
        Assert::true($this->lastGameInitialized);

        return $this->lastGame;
    }

    public function withLastGame(?Game $game) : self
    {
        $this->lastGame = $game;
        $this->lastGameInitialized = true;

        return $this;
    }

    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->displayName(),
        ];
    }

    public function ageNow() : int
    {
        $yearsPassed = Date::age($this->createdAt)->y;
        
        return $this->age + $yearsPassed;
    }
}
