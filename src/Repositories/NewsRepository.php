<?php

namespace App\Repositories;

use App\Collections\NewsCollection;
use App\Models\News;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\NewsSourceRepository;

class NewsRepository extends NewsSourceRepository implements NewsRepositoryInterface
{
    protected function entityClass() : string
    {
        return News::class;
    }

    public function get(?int $id) : ?News
    {
        return $this->getEntity($id);
    }

    public function getProtected(?int $id) : ?News
    {
        return $this->getProtectedEntity($id);
    }

    // SearchableRepositoryInterface

    public function search(string $searchQuery) : NewsCollection
    {
        return NewsCollection::from(
            $this
                ->publishedQuery()
                ->search($searchQuery, '(title like ?)')
                ->orderByAsc('title')
        );
    }
}
