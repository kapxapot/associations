<?php

namespace App\Collections;

use App\Models\WordRelation;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Util\Sort;

class WordRelationCollection extends DbModelCollection
{
    protected string $class = WordRelation::class;

    /**
     * Returns 1st primary relation.
     */
    public function primary(): ?WordRelation
    {
        return $this->primaries()->first();
    }

    /**
     * Filters primary relations.
     *
     * @return static
     */
    public function primaries(): self
    {
        return $this->where(
            fn (WordRelation $wr) => $wr->isPrimary()
        );
    }

    /**
     * Filters word form relations.
     *
     * @return static
     */
    public function wordForms(): self
    {
        return $this->where(
            fn (WordRelation $wr) => $wr->isWordForm()
        );
    }

    public function mainWords(): WordCollection
    {
        return WordCollection::from(
            $this->map(
                fn (WordRelation $wr) => $wr->mainWord()
            )
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
