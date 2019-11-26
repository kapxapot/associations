<?php

namespace App\Models;

use App\Models\Interfaces\DictWordInterface;
use Plasticode\Models\DbModel;

class YandexDictWord extends DbModel implements DictWordInterface
{
    public static function getByWord(Word $word) : ?self
    {
        return self::query()
            ->where('word_id', $word->getId())
            ->one();
    }

    public static function getByWordStr(Language $language, string $wordStr) : ?self
    {
        return self::query()
            ->where('language_id', $language->getId())
            ->where('word', $wordStr)
            ->one();
    }

    public function isValid() : bool
    {
        return !is_null($this->pos);
    }
}
