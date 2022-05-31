<?php

namespace App\Data;

use Plasticode\Collections\Generic\Collection;
use Plasticode\Data\Query;
use Plasticode\Data\QueryInfo;

class QueryReportBuilder
{
    public function buildReport(): array
    {
        $groups = Collection::make(Query::getLog())
            ->group(
                fn (QueryInfo $qi) => $qi->query
            );

        $orderedGroups = Collection::make(array_values($groups))
            ->desc(
                fn (Collection $c) => $c->count()
            )
            ->map(
                fn (Collection $c) => [
                    'query' => $c->first()->query,
                    'count' => $c->count(),
                    'entries' => $c,
                ]
            );

        $total = $orderedGroups
            ->numerize(
                fn (array $a) => $a['count']
            )
            ->sum();

        return [
            // 'actual_query_count' => Query::getQueryCount(),
            'total' => $total,
            'groups' => $orderedGroups,
        ];
    }
}
