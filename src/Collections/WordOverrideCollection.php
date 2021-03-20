<?php

namespace App\Collections;

use App\Models\WordOverride;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Util\Sort;

class WordOverrideCollection extends DbModelCollection
{
    protected string $class = WordOverride::class;

    public function latest() : ?WordOverride
    {
        return $this
            ->desc(
                fn (CreatedAtInterface $c) => $c->createdAt,
                Sort::DATE
            )
            ->first();
    }
}
