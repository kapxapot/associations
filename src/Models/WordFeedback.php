<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Util\Date;

class WordFeedback extends DbModel
{
    use Created;
    
    public static function getByWord(Word $word) : Query
    {
        return self::baseQuery()
            ->where('word_id', $word->getId());
    }
    
    public static function filterDisliked(Query $query) : Query
    {
        return $query->where('dislike', 1);
    }
    
    public static function filterMature(Query $query) : Query
    {
        return $query->where('mature', 1);
    }

    public static function filterByCreator(Query $query, User $user) : Query
    {
        return $query->where('created_by', $user->getId());
    }
    
    public static function getByWordAndUser(Word $word, User $user) : ?self
    {
        $query = self::getByWord($word);
        return self::filterByCreator($query, $user)->one();
    }
    
    public function word() : Word
    {
        return Word::get($this->wordId);
    }
    
    public function isDisliked() : bool
    {
        return $this->dislike === 1;
    }
    
    public function hasTypo() : bool
    {
        return strlen($this->typo) > 0;
    }
    
    public function duplicate()
    {
        return Word::get($this->duplicateId);
    }
    
    public function isMature() : bool
    {
        return $this->mature === 1;
    }

    public function updatedAtIso()
    {
        return Date::iso($this->updatedAt);
    }
}
