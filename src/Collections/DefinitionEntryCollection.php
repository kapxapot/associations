<?php

namespace App\Collections;

use App\Semantics\Definition\DefinitionEntry;
use App\Semantics\Interfaces\PartOfSpeechableInterface;
use Plasticode\Collections\Generic\TypedCollection;

class DefinitionEntryCollection extends TypedCollection implements PartOfSpeechableInterface
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
