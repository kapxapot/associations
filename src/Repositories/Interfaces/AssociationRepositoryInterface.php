<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationCollection;
use App\Models\Association;
use App\Models\Language;
use App\Models\Word;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;

interface AssociationRepositoryInterface extends FilteringRepositoryInterface, LanguageElementRepositoryInterface
{
    public function get(?int $id): ?Association;

    public function save(Association $association): Association;

    public function store(array $data): Association;

    public function getAllByLanguage(Language $language): AssociationCollection;

    public function getAllByWord(Word $word): AssociationCollection;

    /**
     * Looks for an association by ordered pair of words.
     *
     * Use `AssociationService->getByPair()` for convenience
     * (it doesn't require the pair to be ordered).
     */
    public function getByOrderedPair(Word $first, Word $second): ?Association;

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(int $ttlMin, int $limit = 0): AssociationCollection;

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): AssociationCollection;
}
