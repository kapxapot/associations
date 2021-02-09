<?php

namespace App\Collections;

use App\Semantics\PartOfSpeech;
use Plasticode\Collections\Generic\TypedCollection;

class PartOfSpeechCollection extends TypedCollection
{
    protected string $class = PartOfSpeech::class;

    public function isAnyGood(): bool
    {
        return $this->any(
            fn (PartOfSpeech $p) => $p->isGood()
        );
    }
}
