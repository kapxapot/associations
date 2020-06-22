<?php

namespace App\Repositories;

use App\Models\News;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;

class NewsRepository extends TaggedRepository implements NewsRepositoryInterface
{
    protected string $entityClass = News::class;
}
