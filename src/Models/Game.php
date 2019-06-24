<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Util\Date;

class Game extends DbModel
{
    // queries
    
    public static function getByUser(User $user) : Query
    {
        return self::baseQuery()
            ->where('user_id', $user->getId());
    }
    
    // properties
    
    public function turns() : Query
    {
        return Turn::getByGame($this);
    }
    
    public function turnsCountStr() : string
    {
	    return self::$cases->caseForNumber('ход', $this->turns()->count());
    }
    
    public function lastTurn()
    {
        return $this->turns()->one();
    }

    public function beforeLastTurn()
    {
        return $this->lastTurn() !== null
            ? $this->lastTurn()->prev()
            : null;
    }
    
    public function words() : Collection
    {
        return $this->turns()->all()->map(function ($turn) {
            return $turn->word(); 
        });
    }
    
    public function lastTurnWord() {
	    return $this->lastTurn() !== null
	        ? $this->lastTurn()->word()
	        : null;
    }
	
	public function beforeLastTurnWord() {   
	    return $this->beforeLastTurn() !== null
	        ? $this->beforeLastTurn()->word()
	        : null;
    }
    
    public function language()
    {
        return Language::get($this->languageId);
    }
    
    public function user()
    {
        return self::getUser($this->userId);
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
        return $this->isFinished() && $this->lastTurn() != null && $this->lastTurn()->isPlayerTurn();
    }
    
    public function isWonByAi() : bool
    {
        return $this->isFinished() && $this->lastTurn() != null && $this->lastTurn()->isAiTurn();
    }
    
    public function players() : Collection
    {
        return $this
            ->turns()
            ->all()
            ->map(function ($turn) {
                return $turn->user();
            })
            ->distinct();
    }

    public function containsWordStr(string $wordStr) : bool
    {
        $word = Word::findInLanguage($this->language, $wordStr);

        // new word
        if ($word === null) {
            return true;
        }

        return $this->containsWord($word);
    }

    public function containsWord(Word $word) : bool
    {
        return $this
            ->words()
            ->any('id', $word->getId());
    }
    
    public function url()
    {
        return self::$linker->game($this);
    }
    
    public function displayName()
    {
        return 'Игра #' . $this->getId();
    }
    
    public function createdAtIso()
    {
        return Date::iso($this->createdAt);
    }
    
    public function finishedAtIso()
    {
        return Date::iso($this->finishedAt);
    }
}
