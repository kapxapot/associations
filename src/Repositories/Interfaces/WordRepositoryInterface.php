<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordCollection;
use App\Models\Language;
use App\Models\Word;

interface WordRepositoryInterface extends LanguageElementRepositoryInterface
{
    function get(?int $id) : ?Word;
    function save(Word $word) : Word;
    function getAllByLanguage(Language $language) : WordCollection;
    function findInLanguage(Language $language, ?string $wordStr) : ?Word;

    function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = null
    ) : WordCollection;
}
