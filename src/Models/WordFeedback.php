<?php

namespace App\Models;

use Plasticode\Query;

class WordFeedback extends Feedback
{
    public static function getByWord(Word $word) : Query
    {
        return self::baseQuery()
            ->where('word_id', $word->getId());
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
    
    public function hasTypo() : bool
    {
        return strlen($this->typo) > 0;
    }
    
    public function duplicate()
    {
        return Word::get($this->duplicateId);
    }
}
