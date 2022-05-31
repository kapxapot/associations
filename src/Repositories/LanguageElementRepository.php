<?php

namespace App\Repositories;

use App\Collections\LanguageElementCollection;
use App\Models\Language;
use App\Models\LanguageElement;
use App\Models\User;
use App\Repositories\Interfaces\LanguageElementRepositoryInterface;
use App\Repositories\Traits\CollectingRepository;
use App\Repositories\Traits\WithLanguageRepository;
use App\Semantics\Scope;
use App\Semantics\Severity;
use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Data\Query;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Traits\Convert\ToBit;

abstract class LanguageElementRepository extends IdiormRepository implements LanguageElementRepositoryInterface
{
    use CollectingRepository;
    use CreatedRepository;
    use ToBit;
    use WithLanguageRepository;

    protected string $scopeField = 'scope';
    protected string $severityField = 'severity';
    protected string $updatedAtField = 'updated_at';
    protected string $scopeUpdatedAtField = 'scope_updated_at';

    protected function collect(ArrayableInterface $arrayable): LanguageElementCollection
    {
        return LanguageElementCollection::from($arrayable);
    }

    public function get(?int $id): ?LanguageElement
    {
        return $this->getEntity($id);
    }

    public function getAllByLanguage(Language $language): LanguageElementCollection
    {
        return $this->collect(
            $this->byLanguageQuery($language)
        );
    }

    public function getAllByIds(NumericCollection $ids): LanguageElementCollection
    {
        return $this->collect(
            $this
                ->query()
                ->whereIn($this->idField(), $ids)
        );
    }

    public function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ): LanguageElementCollection
    {
        $query = $this->byLanguageQuery($language);

        return $this->collect(
            $this->filterByCreator($query, $user)
        );
    }

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes.
     */
    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): LanguageElementCollection
    {
        $query = $this
            ->query()
            ->whereRaw(sprintf(
                '(%s < date_sub(now(), interval %d minute))',
                $this->updatedAtField,
                $ttlMin
            ))
            ->limit($limit)
            ->orderByAsc($this->updatedAtField);

        return $this->collect($query);
    }

    public function getAllByScope(
        int $scope,
        ?Language $language = null
    ): LanguageElementCollection
    {
        return $this->collect(
            $this->byScopeQuery($scope, $language)
        );
    }

    public function getAllApproved(
        ?Language $language = null
    ): LanguageElementCollection
    {
        return $this->collect(
            $this->approvedQuery($language)
        );
    }

    /**
     * Filters fuzzy public & not mature elements, ordered by `scope_updated_at` DESC.
     */
    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): LanguageElementCollection
    {
        $query = $this
            ->approvedQuery($language)
            ->apply(
                fn (Query $q) => $this->filterNotMature($q)
            )
            ->limit($limit)
            ->orderByDesc($this->scopeUpdatedAtField);

        return $this->collect($query);
    }

    // queries

    protected function byScopeQuery(int $scope, ?Language $language = null): Query
    {
        return $this
            ->byLanguageQuery($language)
            ->apply(
                fn (Query $q) => $this->filterByScope($q, $scope)
            );
    }

    protected function approvedQuery(?Language $language = null): Query
    {
        return $this
            ->byLanguageQuery($language)
            ->apply(
                fn (Query $q) => $this->filterApproved($q)
            );
    }

    // filters

    /**
     * Filters by fuzzy public scopes.
     */
    protected function filterApproved(Query $query): Query
    {
        return $this->filterByScope($query, ...Scope::allFuzzyPublic());
    }

    protected function filterNotMature(Query $query): Query
    {
        return $this->filterBySeverityNot($query, Severity::MATURE);
    }

    // generic filters

    protected function filterByScope(Query $query, int ...$scopes): Query
    {
        return $query->whereIn($this->scopeField, $scopes);
    }

    protected function filterByScopeNot(Query $query, int ...$scopes): Query
    {
        return $query->whereNotIn($this->scopeField, $scopes);
    }

    protected function filterBySeverity(Query $query, int ...$severities): Query
    {
        return $query->whereIn($this->severityField, $severities);
    }

    protected function filterBySeverityNot(Query $query, int ...$severities): Query
    {
        return $query->whereNotIn($this->severityField, $severities);
    }
}
