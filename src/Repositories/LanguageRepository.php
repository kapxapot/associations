<?php

namespace App\Repositories;

use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class LanguageRepository extends IdiormRepository implements LanguageRepositoryInterface
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
