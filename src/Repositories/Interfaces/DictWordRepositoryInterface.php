<?php

namespace App\Repositories\Interfaces;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;

interface DictWordRepositoryInterface
{
    function save(DictWordInterface $dictWord) : DictWordInterface;
    function getByWord(Word $word) : ?DictWordInterface;
    function getByWordStr(Language $language, string $wordStr) : ?DictWordInterface;
}
