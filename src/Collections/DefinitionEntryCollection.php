<?php

namespace App\Collections;

use App\Semantics\Definition\DefinitionEntry;
use App\Semantics\PartOfSpeech;
use Plasticode\Collections\Generic\TypedCollection;

class DefinitionEntryCollection extends TypedCollection
{
    protected string $class = DefinitionEntry::class;

    public function partsOfSpeech(): PartOfSpeechCollection
    {
        return PartOfSpeechCollection::from(
            $this
                ->map(
                    fn (DefinitionEntry $de) => $de->partOfSpeech()
                )
                ->clean()
                ->distinctBy(
                    fn (?PartOfSpeech $p) => $p->name()
                )
        );
    }
}
