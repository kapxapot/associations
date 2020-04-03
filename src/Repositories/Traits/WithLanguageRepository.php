<?php

namespace App\Repositories\Traits;

use App\Models\Language;
use Plasticode\Query;

trait WithLanguageRepository
{
    protected string $languageIdField = 'language_id';

    protected abstract function query() : Query;

    protected function getByLanguageQuery(?Language $language) : Query
    {
        return $this->filterByLanguage($this->query(), $language);
    }

    protected function filterByLanguage(Query $query, ?Language $language) : Query
    {
        return $language
            ? $query->where($this->languageIdField, $language->getId())
            : $query;
    }
}
