<?php

namespace App\Semantics\Definition;

class DefinitionAggregate
{
    /** @var DefinitionEntry[] */
    private array $entries = [];

    /**
     * @return DefinitionEntry[]
     */
    public function entries(): array
    {
        return $this->entries;
    }

    /**
     * @return $this
     */
    public function addEntry(DefinitionEntry $entry): self
    {
        $this->entries[] = $entry;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->entries);
    }
}
