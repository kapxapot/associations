<?php

namespace App\Repositories;

use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class LanguageRepository extends IdiormRepository implements LanguageRepositoryInterface
{
    protected string $entityClass = Language::class;

    public function get(?int $id) : ?Language
    {
        return $this->getEntity($id);
    }

    public function getByCode(?string $code) : ?Language
    {
        return $this
            ->query()
            ->where('code', $code)
            ->one();
    }
}
