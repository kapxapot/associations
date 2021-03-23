<?php

namespace App\Collections;

use App\Models\WordOverride;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Interfaces\DbModelInterface;

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
            fn (DbModelInterface $m) => $m->getId()
        );
    }
}
