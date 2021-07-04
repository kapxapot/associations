<?php

namespace App\Repositories\Traits;

use App\Search\SearchParams;
use Plasticode\Data\Query;

trait SearchRepository
{
    public function applySearchParams(Query $query, SearchParams $searchParams): Query
    {
        return $query
            ->applyIf(
                $searchParams->hasFilter(),
                fn (Query $q) => $this->applyFilter($q, $searchParams->filter())
            )
            ->applyIf(
                $searchParams->hasSort(),
                fn (Query $q) => $q->withSort($searchParams->sort())
            )
            ->applyIf(
                $searchParams->hasOffset(),
                fn (Query $q) => $q->offset($searchParams->offset())
            )
            ->applyIf(
                $searchParams->hasLimit(),
                fn (Query $q) => $q->limit($searchParams->limit())
            );
    }

    abstract protected function applyFilter(Query $query, string $filter): Query;
}
