<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordCollection;
use App\Models\Language;
use App\Models\Word;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;
use Plasticode\Search\SearchParams;

interface WordRepositoryInterface extends FilteringRepositoryInterface, LanguageElementRepositoryInterface
{
    public function get(?int $id): ?Word;

    public function save(Word $word): Word;

    public function store(array $data): Word;

    public function getAllByLanguage(Language $language): WordCollection;

    /**
     * Finds the word by string in the specified language strictly by `word_bin` field.
     *
     * - Normalized word string expected.
     */
    public function findInLanguageStrict(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null
    ): ?Word;

    /**
     * Finds the word by string in the specified language.
     *
     * - Searches by `word_bin` and `original_word` fields by default.
     * - In strict mode (`$strict === true`) searches strictly by `word_bin`.
     * - Normalized word string expected.
     */
    public function findInLanguage(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null,
        bool $strict = false
    ): ?Word;

    public function searchAllNotMature(
        SearchParams $searchParams,
        ?Language $language = null
    ): WordCollection;

    public function getNotMatureCount(
        ?Language $language = null,
        ?string $substr = null
    ): int;

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(int $ttlMin, int $limit = 0): WordCollection;

    public function getAllByScope(
        int $scope,
        ?Language $language = null
    ): WordCollection;

    public function getAllApproved(?Language $language = null): WordCollection;

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): WordCollection;

    /**
     * Returns words without corresponding dict words.
     */
    public function getAllUnchecked(int $limit = 0): WordCollection;

    /**
     * Returns words without definitions.
     */
    public function getAllUndefined(int $limit = 0): WordCollection;

    /**
     * Returns all words having the provided word as `main`.
     */
    public function getAllByMain(Word $word): WordCollection;
}
