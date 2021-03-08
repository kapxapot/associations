<?php

namespace App\Models\DTO\Search;

use Plasticode\Collections\Generic\Collection;

class SearchResult
{
    private Collection $data;
    private int $totalCount;
    private int $filteredCount;

    public function __construct(
        Collection $data,
        int $totalCount,
        ?int $filteredCount = null
    )
    {
        $this->data = $data;
        $this->totalCount = $totalCount;
        $this->filteredCount = $filteredCount ?? $totalCount;
    }

    public function data(): Collection
    {
        return $this->data;
    }

    public function totalCount(): int
    {
        return $this->totalCount;
    }

    public function filteredCount(): int
    {
        return $this->filteredCount;
    }
}
