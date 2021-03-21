<?php

namespace App\Collections;

use App\Models\WordOverride;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Util\Sort;

class WordOverrideCollection extends DbModelCollection
{
    protected string $class = WordOverride::class;

    public function latest(): ?WordOverride
    {
        return $this->sort()->first();
    }

    /**
     * Sorts the collection by createdAt DESC.
     *
     * @return static
     */
    public function sort(): self
    {
        return $this->desc(
            fn (CreatedAtInterface $c) => $c->createdAt,
            Sort::DATE
        );
    }
}
