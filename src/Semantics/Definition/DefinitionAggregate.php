<?php

namespace App\Semantics\Definition;

use App\Collections\DefinitionEntryCollection;
use App\Collections\PartOfSpeechCollection;
use App\Models\Language;

class DefinitionAggregate
{
    private Language $language;
    private DefinitionEntryCollection $entries;

    public function __construct(
        Language $language
    )
    {
        $this->language = $language;
        $this->entries = DefinitionEntryCollection::empty();
    }

    public function language(): Language
    {
        return $this->language;
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
