<?php

namespace App\Repositories\Interfaces;

use App\Collections\LanguageElementCollection;
use App\Models\Language;
use App\Models\LanguageElement;
use App\Models\User;
use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface LanguageElementRepositoryInterface extends ChangingRepositoryInterface, WithLanguageRepositoryInterface
{
    public function get(?int $id): ?LanguageElement;

    public function getAllByLanguage(Language $language): LanguageElementCollection;

    /**
     * Loads several elements by their ids.
     */
    public function getAllByIds(NumericCollection $ids): LanguageElementCollection;

    public function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ): LanguageElementCollection;

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): LanguageElementCollection;

    public function getAllByScope(
        int $scope,
        ?Language $language = null
    ): LanguageElementCollection;

    public function getAllApproved(?Language $language = null): LanguageElementCollection;

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): LanguageElementCollection;
}
