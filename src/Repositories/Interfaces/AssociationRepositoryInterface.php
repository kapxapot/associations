<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationCollection;
use App\Models\Association;
use App\Models\Language;
use App\Models\Word;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;

interface AssociationRepositoryInterface extends FilteringRepositoryInterface, LanguageElementRepositoryInterface
{
    function get(?int $id): ?Association;

    function save(Association $association): Association;

    function store(array $data): Association;

    function getAllByLanguage(Language $language): AssociationCollection;

    function getAllByWord(Word $word): AssociationCollection;

    function getByPair(Word $first, Word $second): ?Association;

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    function getAllOutOfDate(int $ttlMin, int $limit = 0): AssociationCollection;

    function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): AssociationCollection;
}
