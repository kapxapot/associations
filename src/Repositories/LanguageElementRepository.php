<?php

namespace App\Repositories;

use App\Collections\LanguageElementCollection;
use App\Models\Language;
use App\Models\LanguageElement;
use App\Models\User;
use App\Repositories\Interfaces\LanguageElementRepositoryInterface;
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Traits\Convert\ToBit;

abstract class LanguageElementRepository extends IdiormRepository implements LanguageElementRepositoryInterface
{
    use CreatedRepository;
    use ToBit;
    use WithLanguageRepository;

    public function get(?int $id) : ?LanguageElement
    {
        return $this->getEntity($id);
    }

    public function getAllByLanguage(Language $language) : LanguageElementCollection
    {
        return LanguageElementCollection::from(
            $this->getByLanguageQuery($language)
        );
    }

    public function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ) : LanguageElementCollection
    {
        $query = $this->getByLanguageQuery($language);

        return LanguageElementCollection::from(
            $this->filterByCreator($query, $user)
        );
    }

    public function getAllPublic(
        ?Language $language = null
    ) : LanguageElementCollection
    {
        $query = $this->getByLanguageQuery($language);
        
        return LanguageElementCollection::from(
            $this->filterNotMature($query)
        );
    }

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ) : LanguageElementCollection
    {
        return LanguageElementCollection::from(
            $this
                ->query()
                ->whereRaw(
                    '(updated_at < date_sub(now(), interval ' . $ttlMin . ' minute))'
                )
                ->limit($limit)
                ->orderByAsc('updated_at')
        );
    }

    public function getAllApproved(
        ?Language $language = null
    ) : LanguageElementCollection
    {
        return LanguageElementCollection::from(
            $this->approvedQuery($language)
        );
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ) : LanguageElementCollection
    {
        return LanguageElementCollection::from(
            $this
                ->publicQuery($language)
                ->limit($limit)
        );
    }

    // queries

    protected function publicQuery(?Language $language = null) : Query
    {
        return $this
            ->approvedQuery($language)
            ->apply(
                fn (Query $q) => $this->filterNotMature($q)
            );
    }

    protected function approvedQuery(?Language $language = null) : Query
    {
        return $this
            ->getByLanguageQuery($language)
            ->apply(
                fn (Query $q) => $this->filterApproved($q)
            )
            ->orderByDesc('approved_updated_at');
    }

    // filters

    protected function filterApproved(Query $query, bool $approved = true) : Query
    {
        return $query->where('approved', self::toBit($approved));
    }

    protected function filterNotApproved(Query $query) : Query
    {
        return $this->filterApproved($query, false);
    }

    protected function filterMature(Query $query, bool $mature = true) : Query
    {
        return $query->where('mature', self::toBit($mature));
    }

    protected function filterNotMature(Query $query) : Query
    {
        return $this->filterMature($query, false);
    }
}
