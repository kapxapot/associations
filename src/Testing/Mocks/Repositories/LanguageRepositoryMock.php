<?php

namespace App\Testing\Mocks\Repositories;

use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class LanguageRepositoryMock implements LanguageRepositoryInterface
{
    private DbModelCollection $languages;

    public function __construct(
        ArraySeederInterface $seeder
    )
    {
        $this->languages = DbModelCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?Language
    {
        return $this->languages->first('id', $id);
    }

    public function getByCode(?string $code) : ?Language
    {
        return $this->languages->first('code', $code);
    }
}
