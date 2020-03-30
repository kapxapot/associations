<?php

namespace App\Models;

/**
 * @property string|null $typo
 */
class WordFeedback extends Feedback
{
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
    
    public function duplicate() : ?Word
    {
        return Word::get($this->duplicateId);
    }
}
