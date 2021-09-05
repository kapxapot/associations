<?php

namespace App\Repositories\Traits;

use App\Models\Language;
use Plasticode\Data\Query;

trait WithLanguageRepository
{
    protected string $languageIdField = 'language_id';

    abstract protected function query(): Query;

    public function getCountByLanguage(Language $language): int
    {
        return $this
            ->byLanguageQuery($language)
            ->count();
    }

    protected function byLanguageQuery(?Language $language): Query
    {
        return $this->filterByLanguage($this->query(), $language);
    }

    protected function filterByLanguage(Query $query, ?Language $language): Query
    {
        return $language
            ? $query->where($this->languageIdField, $language->getId())
            : $query;
    }
}
