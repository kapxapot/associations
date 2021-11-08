<?php

namespace App\Collections;

use App\Models\Association;
use App\Models\Word;
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

    public function sortByOtherThan(Word $word): self
    {
        return $this->ascStr(
            fn (Association $a) => $a->otherWord($word)->word
        );
    }
}
