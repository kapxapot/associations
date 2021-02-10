<?php

namespace App\Collections;

use App\Semantics\Definition\DefinitionEntry;
use Plasticode\Collections\Generic\TypedCollection;

class DefinitionEntryCollection extends TypedCollection
{
    protected string $class = DefinitionEntry::class;

    public function partsOfSpeech(): PartOfSpeechCollection
    {
        return PartOfSpeechCollection::from(
            $this->cleanMap(
                fn (DefinitionEntry $de) => $de->partOfSpeech()
            )
        )
        ->distinct();
    }
}
