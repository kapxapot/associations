<?php

namespace App\Repositories\Interfaces;

use App\Collections\PageCollection;
use App\Models\Page;
use Plasticode\Repositories\Interfaces\Generic\ParentedRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\SearchableNewsSourceRepositoryInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface as BasePageRepositoryInterface;

interface PageRepositoryInterface extends BasePageRepositoryInterface, SearchableNewsSourceRepositoryInterface, ParentedRepositoryInterface
{
    public function get(?int $id): ?Page;

    public function getProtected(?int $id): ?Page;

    public function getBySlug(?string $slug): ?Page;

    public function getChildren(Page $parent): PageCollection;

    /**
     * Returns all published orphans.
     */
    public function getAllPublishedOrphans(): PageCollection;

    public function search(string $query): PageCollection;
}
