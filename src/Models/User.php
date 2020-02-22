<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\User as UserBase;
use Plasticode\Util\Date;

class User extends UserBase
{
    public function games() : Query
    {
        return Game::getByUser($this);
    }
    
    public function currentGame() : ?Game
    {
        return $this->games()
            ->whereNull('finished_at')
            ->orderByDesc('id')
            ->one();
    }
    
    public function lastGame() : ?Game
    {
        return $this->games()
            ->orderByDesc('id')
            ->one();
    }
    
    public function createdWords() : Query
    {
        return Word::getCreatedByUser($this);
    }
    
    public function turns(Language $language = null) : Query
    {
        return Turn::getByUser($this, $language);
    }
    
    public function usedWords(Language $language = null) : Collection
    {
        return $this
            ->turns($language)
            ->all()
            ->map(
                function ($turn) {
                    return $turn->word();
                }
            )
            ->distinct();
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
    
    public function isMature() : bool
    {
        return $this->lazy(
            function () {
                $matureAge = self::getSettings('users.mature_age', 16);
        
                return $this->ageNow >= $matureAge;
            }
        );
    }
}
