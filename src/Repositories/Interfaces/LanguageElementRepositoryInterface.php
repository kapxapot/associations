<?php

namespace App\Repositories\Interfaces;

use App\Collections\LanguageElementCollection;
use App\Models\Language;
use App\Models\User;

interface LanguageElementRepositoryInterface
{
    function getAllByLanguage(Language $language) : LanguageElementCollection;

    function getByLanguageCount(Language $language) : int;

    function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ) : LanguageElementCollection;

    function getAllPublic(?Language $language = null) : LanguageElementCollection;

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    function getAllOutOfDate(int $ttlMin) : LanguageElementCollection;

    function getAllApproved(
        ?Language $language = null
    ) : LanguageElementCollection;

    function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = null
    ) : LanguageElementCollection;
}
