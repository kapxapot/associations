<?php

namespace App\Repositories\Interfaces;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;
use Plasticode\Repositories\Interfaces\Basic\ChangingRepositoryInterface;

interface YandexDictWordRepositoryInterface extends ChangingRepositoryInterface, DictWordRepositoryInterface
{
    function get(?int $id) : ?YandexDictWord;

    /**
     * @param YandexDictWord $dictWord
     */
    function save(DictWordInterface $dictWord) : YandexDictWord;

    function getByWord(Word $word) : ?YandexDictWord;
    function getByWordStr(Language $language, string $wordStr) : ?YandexDictWord;
}
