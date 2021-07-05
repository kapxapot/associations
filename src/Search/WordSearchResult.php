<?php

namespace App\Search;

use App\Collections\WordCollection;
use App\Models\Word;
use JsonSerializable;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Search\SearchResult;

class WordSearchResult extends SearchResult implements ArrayableInterface, JsonSerializable
{
    public function __construct(
        WordCollection $data,
        int $totalCount,
        ?int $filteredCount = null
    )
    {
        parent::__construct($data, $totalCount, $filteredCount);
    }

    public function data(): WordCollection
    {
        return parent::data();
    }

    // ArrayableInterface

    public function toArray(): array
    {
        return [
            'data' => $this
                ->data()
                ->map(
                    fn (Word $w) => $w->serialize()
                ),
            'recordsTotal' => $this->totalCount(),
            'recordsFiltered' => $this->filteredCount(),
        ];
    }

    // JsonSerializable

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
