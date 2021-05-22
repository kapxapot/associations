<?php

namespace App\Collections;

use App\Models\WordRelation;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Util\Sort;

class WordRelationCollection extends DbModelCollection
{
    protected string $class = WordRelation::class;

    /**
     * Filters primary relations.
     *
     * @return static
     */
    public function primary(): self
    {
        return $this->where(
            fn (WordRelation $wr) => $wr->isPrimary()
        );
    }

    /**
     * Sorts the collection by `updatedAt` descending.
     * 
     * @return static
     */
    public function descByUpdate(): self
    {
        return $this->desc(
            fn (WordRelation $wr) => $wr->updatedAt,
            Sort::DATE
        );
    }
}
