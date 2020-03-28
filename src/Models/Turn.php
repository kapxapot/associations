<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;

class Turn extends DbModel
{
    use CreatedAt;

    protected static $sortField = 'id';
    protected static $sortReverse = true;

    private ?User $user = null;
    
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

    public static function filterByUser(Query $query, User $user) : Query
    {
        return $query->where('user_id', $user->getId());
    }
    
    public static function getByUser(User $user, Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
            
        return self::filterByUser($query, $user);
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
    
    public function user() : ?User
    {
        return self::$container->userRepository->get($this->userId);
    }

    public function isBy(User $user) : bool
    {
        return $this->user->equals($user);
    }
    
    public function association() : ?Association
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
    
    public function prev() : ?Turn
    {
        return self::get($this->prevTurnId);
    }
    
    public function isFinished() : bool
    {
        return $this->finishedAt != null;
    }
}
