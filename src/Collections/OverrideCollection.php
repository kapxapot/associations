<?php

namespace App\Collections;

use App\Models\Override;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Interfaces\DbModelInterface;

class OverrideCollection extends DbModelCollection
{
    protected string $class = Override::class;

    public function latest(): ?Override
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
