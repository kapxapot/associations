<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationCollection;
use App\Models\Association;
use App\Models\Language;
use App\Models\Word;

interface AssociationRepositoryInterface extends LanguageElementRepositoryInterface
{
    function get(?int $id) : ?Association;
    function getAllByLanguage(Language $language) : AssociationCollection;
    function getAllByWord(Word $word) : AssociationCollection;
    function getByPair(Word $first, Word $second) : ?Association;

    function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = null
    ) : AssociationCollection;
}
