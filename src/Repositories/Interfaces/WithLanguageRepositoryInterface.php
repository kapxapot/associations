<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;
use Plasticode\Repositories\Interfaces\Basic\RepositoryInterface;

interface WithLanguageRepositoryInterface extends RepositoryInterface
{
    function getCountByLanguage(Language $language): int;
}
