<?php

namespace App\Repositories\Interfaces;

use App\Collections\NewsCollection;
use App\Models\News;
use Plasticode\Repositories\Interfaces\Generic\SearchableNewsSourceRepositoryInterface;

interface NewsRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    function get(?int $id): ?News;
    function getProtected(?int $id): ?News;
    function search(string $searchQuery): NewsCollection;
}
