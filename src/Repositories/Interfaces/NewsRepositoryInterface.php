<?php

namespace App\Repositories\Interfaces;

use App\Collections\NewsCollection;
use App\Models\News;
use Plasticode\Repositories\Interfaces\Generic\SearchableNewsSourceRepositoryInterface;

interface NewsRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    public function get(?int $id): ?News;

    public function getProtected(?int $id): ?News;

    public function search(string $query): NewsCollection;
}
