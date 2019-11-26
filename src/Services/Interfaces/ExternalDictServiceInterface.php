<?php

namespace App\Services\Interfaces;

use App\Models\Language;
use App\Models\Word;
use App\Models\Interfaces\DictWordInterface;

interface ExternalDictServiceInterface
{
    public function getWord(Word $word) : ?DictWordInterface;
    public function getWordStr(Language $language, string $wordStr) : ?DictWordInterface;
}
