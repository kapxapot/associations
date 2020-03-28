<?php

namespace App\Repositories;

use App\Models\Language;
use App\Models\User;
use App\Repositories\Interfaces\LanguageElementRepositoryInterface;
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Collection;
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
    ) : Collection
    {
        $query = $this->getByLanguageQuery($language);

        return $this
            ->filterByCreator($query, $user)
            ->all();
    }

    public function getAllPublic(?Language $language = null) : Collection
    {
        $query = $this->getByLanguageQuery($language);
        
        return $this
            ->filterNotMature($query)
            ->all();
    }

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(int $ttlMin) : Collection
    {
        return $this
            ->query()
            ->whereRaw(
                '(updated_at < date_sub(now(), interval ' . $ttlMin . ' minute))'
            )
            ->orderByAsc('updated_at')
            ->all();
    }

    public function getAllApproved(?Language $language = null) : Collection
    {
        $query = $this->getByLanguageQuery($language);
        
        return $this
            ->filterApproved($query)
            ->orderByDesc('approved_updated_at')
            ->all();
    }

    protected function filterApproved(Query $query, bool $approved = true) : Query
    {
        return $query->where('approved', Convert::toBit($approved));
    }

    protected function filterNotApproved(Query $query) : Query
    {
        return $this->filterApproved($query, false);
    }

    protected function filterMature(Query $query, bool $mature = true) : Query
    {
        return $query->where('mature', Convert::toBit($mature));
    }

    protected function filterNotMature(Query $query) : Query
    {
        return $this->filterMature($query, false);
    }
}
