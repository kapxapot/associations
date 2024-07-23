<?php

namespace App\Repositories\Interfaces;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface YandexDictWordRepositoryInterface extends ChangingRepositoryInterface, DictWordRepositoryInterface
{
    public function get(?int $id): ?YandexDictWord;

    /**
     * @param YandexDictWord $dictWord
     */
    public function save(DictWordInterface $dictWord): YandexDictWord;

    public function getByWord(Word $word): ?YandexDictWord;

    public function getByWordStr(Language $language, string $wordStr): ?YandexDictWord;
}
