<?php

namespace App\Collections;

use App\Models\Association;
use Plasticode\Util\Sort;

class AssociationCollection extends LanguageElementCollection
{
    protected string $class = Association::class;

    public function oldest(): ?Association
    {
        return $this
            ->asc(
                fn (Association $a) => $a->createdAt,
                Sort::DATE
            )
            ->first();
    }
}
