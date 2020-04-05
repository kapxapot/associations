<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;

interface YandexDictWordRepositoryInterface
{
    function create(array $data) : YandexDictWord;
    function save(YandexDictWord $word) : YandexDictWord;
    function getByWord(Word $word) : ?YandexDictWord;
    function getByWordStr(Language $language, string $wordStr) : ?YandexDictWord;
}
