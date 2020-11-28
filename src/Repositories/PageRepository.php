<?php

namespace App\Repositories;

use App\Collections\PageCollection;
use App\Models\Page;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\NewsSourceRepository;
use Plasticode\Repositories\Idiorm\Traits\ChildrenRepository;

class PageRepository extends NewsSourceRepository implements PageRepositoryInterface
{
    use ChildrenRepository;

    /**
     * @inheritDoc
     */
    protected function entityClass() : string
    {
        return Page::class;
    }

    public function get(?int $id) : ?Page
    {
        return $this->getEntity($id);
    }

    public function getProtected(?int $id) : ?Page
    {
        return $this->getProtectedEntity($id);
    }

    public function getBySlug(?string $slug) : ?Page
    {
        return $this
            ->query()
            ->where('slug', $slug)
            ->one();
    }

    /**
     * Checks duplicates (for validation).
     */
    public function lookup(string $slug, int $exceptId = 0) : PageCollection
    {
        return PageCollection::from(
            $this
                ->query()
                ->where('slug', $slug)
                ->applyIf(
                    $exceptId > 0,
                    fn (Query $q) => $q->whereNotEqual($this->idField(), $exceptId)
                )
        );
    }

    public function getChildren(Page $parent) : PageCollection
    {
        return PageCollection::from(
            $this
                ->query()
                ->apply(
                    fn (Query $q) => $this->filterByParent($q, $parent->getId())
                )
        );
    }

    /**
     * Returns all published orphans.
     */
    public function getAllPublishedOrphans() : PageCollection
    {
        return PageCollection::from(
            $this
                ->publishedQuery()
                ->apply(
                    fn (Query $q) => $this->filterOrphans($q)
                )
        );
    }

    // SearchableRepositoryInterface

    public function search(string $searchQuery) : PageCollection
    {
        return PageCollection::from(
            $this
                ->publishedQuery()
                ->search($searchQuery, '(slug like ? or title like ?)', 2)
                ->all()
                ->orderByStr('title')
        );
    }

    // queries

    protected function newsSourceQuery() : Query
    {
        return $this->feedQuery();
    }

    /**
     * Published + feed query.
     */
    protected function feedQuery() : Query
    {
        return $this
            ->publishedQuery()
            ->apply(
                fn (Query $q) => $this->filterByFeed($q)
            );
    }

    // filters

    protected function filterByFeed(Query $query) : Query
    {
        return $query->where('show_in_feed', 1);
    }
}
