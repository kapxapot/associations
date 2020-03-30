<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;

interface LanguageRepositoryInterface
{
    function get(?int $id): ?Language;
}
