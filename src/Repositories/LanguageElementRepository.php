<?php

namespace App\Repositories;

use App\Collections\LanguageElementCollection;
use App\Models\Language;
use App\Models\User;
use App\Repositories\Interfaces\LanguageElementRepositoryInterface;
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Util\Convert;

abstract class LanguageElementRepository extends IdiormRepository implements LanguageElementRepositoryInterface
{
    use CreatedRepository, WithLanguageRepository;

    public function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ): LanguageElementCollection
    {
        $query = $this->getByLanguageQuery($language);

        return LanguageElementCollection::from(
            $this->filterByCreator($query, $user)
        );
    }

    public function getAllPublic(
        ?Language $language = null
    ): LanguageElementCollection
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
    public function getAllOutOfDate(int $ttlMin): LanguageElementCollection
    {
        return LanguageElementCollection::from(
            $this
                ->query()
                ->whereRaw(
                    '(updated_at < date_sub(now(), interval ' . $ttlMin . ' minute))'
                )
                ->orderByAsc('updated_at')
        );
    }

    public function getAllApproved(
        ?Language $language = null
    ): LanguageElementCollection
    {
        $query = $this->getByLanguageQuery($language);
        
        return LanguageElementCollection::from(
            $this
                ->filterApproved($query)
                ->orderByDesc('approved_updated_at')
        );
    }

    protected function filterApproved(Query $query, bool $approved = true): Query
    {
        return $query->where('approved', Convert::toBit($approved));
    }

    protected function filterNotApproved(Query $query): Query
    {
        return $this->filterApproved($query, false);
    }

    protected function filterMature(Query $query, bool $mature = true): Query
    {
        return $query->where('mature', Convert::toBit($mature));
    }

    protected function filterNotMature(Query $query): Query
    {
        return $this->filterMature($query, false);
    }
}
