<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;
use App\Models\User;
use Plasticode\Collection;

interface LanguageElementRepositoryInterface
{
    function getAllByLanguage(Language $language) : Collection;

    function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ) : Collection;

    function getAllPublic(?Language $language = null) : Collection;

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    function getAllOutOfDate(int $ttlMin) : Collection;

    function getAllApproved(?Language $language = null) : Collection;
}
