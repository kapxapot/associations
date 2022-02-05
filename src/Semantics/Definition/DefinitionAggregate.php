<?php

namespace App\Semantics\Definition;

use App\Collections\DefinitionEntryCollection;
use App\Collections\PartOfSpeechCollection;
use App\Models\Language;
use App\Models\Word;
use App\Semantics\Interfaces\PartOfSpeechableInterface;
use Plasticode\Collections\Generic\StringCollection;

class DefinitionAggregate implements PartOfSpeechableInterface
{
    private Language $language;
    private Word $word;
    private DefinitionEntryCollection $entries;

    public function __construct(
        Language $language,
        Word $word
    )
    {
        $this->language = $language;
        $this->word = $word;
        $this->entries = DefinitionEntryCollection::empty();
    }

    public function language(): Language
    {
        return $this->language;
    }

    public function word(): Word
    {
        return $this->word;
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
