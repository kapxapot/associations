<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class YandexDictWord extends DbModel
{
    public static function getByWord(Word $word) : ?self
    {
        
    }

    public static function getByWordStr(Language $language, string $wordStr) : ?self
    {

    }
}
