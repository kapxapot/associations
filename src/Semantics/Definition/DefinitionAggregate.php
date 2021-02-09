<?php

namespace App\Semantics\Definition;

use App\Collections\DefinitionEntryCollection;
use App\Collections\PartOfSpeechCollection;

class DefinitionAggregate
{
    private DefinitionEntryCollection $entries;

    public function __construct()
    {
        $this->entries = DefinitionEntryCollection::empty();
    }

    public function entries(): DefinitionEntryCollection
    {
        return $this->entries;
    }

    /**
     * @return $this
     */
    public function addEntry(DefinitionEntry $entry): self
    {
        $this->entries = $this->entries->add($entry);

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->entries->isEmpty();
    }

    public function partsOfSpeech(): PartOfSpeechCollection
    {
        return $this->entries->partsOfSpeech();
    }
}
