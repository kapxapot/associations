<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;

interface WithLanguageRepositoryInterface
{
    function getCountByLanguage(Language $language): int;
}
