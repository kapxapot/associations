<?php

namespace App\Repositories;

use App\Models\Language;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\LanguageRepositoryInterface;

class LanguageRepository extends Repository implements LanguageRepositoryInterface
{
    protected function entityClass(): string
    {
        return Language::class;
    }

    public function get(?int $id): ?Language
    {
        return $this->getEntity($id);
    }

    public function getByCode(?string $code): ?Language
    {
        return $this
            ->query()
            ->where('code', $code)
            ->one();
    }
}
