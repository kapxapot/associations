<?php

namespace App\Testing\Mocks\Repositories;

use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class LanguageRepositoryMock extends RepositoryMock implements LanguageRepositoryInterface
{
    private DbModelCollection $languages;

    public function __construct(
        ArraySeederInterface $seeder
    )
    {
        $this->languages = DbModelCollection::make($seeder->seed());
    }

    public function get(?int $id): ?Language
    {
        return $this->languages->first('id', $id);
    }

    public function getByCode(?string $code): ?Language
    {
        return $this->languages->first('code', $code);
    }
}
