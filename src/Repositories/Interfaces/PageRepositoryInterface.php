<?php

namespace App\Repositories\Interfaces;

use App\Collections\PageCollection;
use App\Models\Page;
use Plasticode\Repositories\Interfaces\Generic\ParentedRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\SearchableNewsSourceRepositoryInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface as BasePageRepositoryInterface;

interface PageRepositoryInterface extends BasePageRepositoryInterface, SearchableNewsSourceRepositoryInterface, ParentedRepositoryInterface
{
    function get(?int $id): ?Page;
    function getProtected(?int $id): ?Page;
    function getBySlug(?string $slug): ?Page;
    function getChildren(Page $parent): PageCollection;

    /**
     * Returns all published orphans.
     */
    function getAllPublishedOrphans(): PageCollection;

    function search(string $searchQuery): PageCollection;
}
