<?php

namespace App\Repositories;

use App\Models\Page;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;

class PageRepository extends TaggedRepository implements PageRepositoryInterface
{
    protected string $entityClass = Page::class;

    public function getBySlug(?string $slug) : ?Page
    {
        return $this
            ->query()
            ->where('slug', $slug)
            ->one();
    }
}
