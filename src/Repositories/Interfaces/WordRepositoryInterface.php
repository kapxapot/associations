<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordCollection;
use App\Models\Language;
use App\Models\Word;

interface WordRepositoryInterface extends LanguageElementRepositoryInterface
{
    function get(?int $id): ?Word;
    function save(Word $word): Word;
    function store(array $data): Word;
    function getAllByLanguage(Language $language): WordCollection;
    function findInLanguage(Language $language, ?string $wordStr): ?Word;

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    function getAllOutOfDate(int $ttlMin, int $limit = 0): WordCollection;

    function getAllApproved(?Language $language = null): WordCollection;

    function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): WordCollection;

    /**
     * Returns words without corresponding dict words.
     */
    function getAllUnchecked(int $limit = 0): WordCollection;
}
