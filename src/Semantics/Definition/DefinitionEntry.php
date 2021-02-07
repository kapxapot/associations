<?php

namespace App\Semantics\Definition;

use Plasticode\Collections\Generic\StringCollection;

class DefinitionEntry
{
    private StringCollection $definitions;

    public function __construct(array $definitions = [])
    {
        $this->definitions = StringCollection::make($definitions);
    }

    public function definitions(): StringCollection
    {
        return $this->definitions;
    }

    /**
     * @return $this
     */
    public function addDefinition(string $definition): self
    {
        $this->definitions = $this->definitions->add($definition);

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->definitions->isEmpty();
    }
}
