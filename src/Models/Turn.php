<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Util\Date;

class Turn extends DbModel
{
    protected static $sortField = 'id';
    protected static $sortReverse = true;
    
    // queries
    
    public static function getByGame(Game $game) : Query
    {
        return self::query()
            ->where('game_id', $game->getId());
    }
    
    public static function getByAssociation(Association $association) : Query
    {
        return self::query()
            ->where('association_id', $association->getId());
    }
    
    public static function getByLanguage(Language $language) : Query
    {
        return self::query()
            ->where('language_id', $language->getId());
    }
    
    public static function getByUser(User $user, Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
            
        return $query
            ->where('user_id', $user->getId());
    }
    
    public static function getByWord(Word $word) : Query
    {
        return self::query()
            ->where('word_id', $word->getId());
    }
    
    // properties
    
    public function game() : Game
    {
        return Game::get($this->gameId);
    }
    
    public function word() : Word
    {
        return Word::get($this->wordId);
    }
    
    public function user()
    {
        return self::getUser($this->userId);
    }
    
    public function association()
    {
        return Association::get($this->associationId);
    }
    
    public function isPlayerTurn() : bool
    {
        return $this->user() !== null;
    }
    
    public function isAiTurn() : bool
    {
        return !$this->isPlayerTurn();
    }
    
    public function prev()
    {
        return self::get($this->prevTurnId);
    }
    
    public function isFinished() : bool
    {
        return $this->finishedAt != null;
    }
    
    public function createdAtIso()
    {
        return Date::iso($this->createdAt);
    }
}
