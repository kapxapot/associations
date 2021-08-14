<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;
use Plasticode\Repositories\Interfaces\Generic\RepositoryInterface;

interface WithLanguageRepositoryInterface extends RepositoryInterface
{
    public function getCountByLanguage(Language $language): int;
}
