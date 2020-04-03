<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;
use App\Models\Word;

interface WordRepositoryInterface extends LanguageElementRepositoryInterface
{
    function get(?int $id) : ?Word;
    function save(Word $word) : Word;
    function findInLanguage(Language $language, ?string $wordStr) : ?Word;
}
