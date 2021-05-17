<?php

namespace App\Repositories\Interfaces;

use App\Models\Language;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface LanguageRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?Language;

    public function getByCode(?string $code): ?Language;
}
