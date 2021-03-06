<?php

namespace App\Semantics\Definition;

use App\Collections\DefinitionEntryCollection;
use App\Collections\PartOfSpeechCollection;
use App\Models\Language;
use App\Semantics\Interfaces\PartOfSpeechableInterface;
use Plasticode\Collections\Generic\StringCollection;

class DefinitionAggregate implements PartOfSpeechableInterface
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

    public function firstDefinition(): ?string
    {
        return $this->flatDefinitions()->first();
    }

    public function flatDefinitions(): StringCollection
    {
        return StringCollection::from(
            $this->entries->flatMap(
                fn (DefinitionEntry $de) => $de->definitions()
            )
        );
    }
}
