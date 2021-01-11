<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface LanguageRepositoryInterface extends GetRepositoryInterface
{
    function get(?int $id): ?Language;
    function getByCode(?string $code): ?Language;
}
