<?php

namespace App\Collections;

use App\Models\WordOverride;

class WordOverrideCollection extends OverrideCollection
{
    protected string $class = WordOverride::class;

    public function latest(): ?WordOverride
    {
        return parent::latest();
    }
}
