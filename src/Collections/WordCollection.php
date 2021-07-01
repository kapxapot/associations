<?php

namespace App\Collections;

use App\Models\Word;

class WordCollection extends LanguageElementCollection
{
    protected string $class = Word::class;

    /**
     * Orders words chronologically by their ids (ASC).
     */
    public function order(): self
    {
        return $this->orderBy(
            fn (Word $w) => $w->getId()
        );
    }

    /**
     * Returns distinct `canonical()` words for collection's words.
     */
    public function canonical(): self
    {
        return self::fromDistinct(
            $this->map(
                fn (Word $w) => $w->canonical()
            )
        );
    }
}
