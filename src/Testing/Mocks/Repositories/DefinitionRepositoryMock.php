<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\DefinitionCollection;
use App\Models\Definition;
use App\Models\Word;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;

class DefinitionRepositoryMock implements DefinitionRepositoryInterface
{
    private DefinitionCollection $definitions;

    private WordRepositoryInterface $wordRepository;

    public function __construct(
        WordRepositoryInterface $wordRepository
    )
    {
        $this->definitions = DefinitionCollection::empty();

        $this->wordRepository = $wordRepository;
    }

    public function get(?int $id): ?Definition
    {
        return $this->definitions->first('id', $id);
    }

    public function save(Definition $definition): Definition
    {
        if ($this->definitions->contains($definition)) {
            return $definition;
        }

        if (!$definition->isPersisted()) {
            $definition->id = $this->definitions->nextId();
        }

        $this->definitions = $this->definitions->add($definition);

        $word = $this->wordRepository->get($definition->wordId);
        $word->withDefinition($definition);

        return $definition->withWord($word);
    }

    public function store(array $data): Definition
    {
        $definition = Definition::create($data);

        return $this->save($definition);
    }

    public function delete(Definition $definition): bool
    {
        $this->definitions = $this->definitions->removeFirst(
            fn (Definition $d) => $d->equals($definition)
        );

        return true;
    }

    public function getByWord(Word $word): ?Definition
    {
        return $this
            ->definitions
            ->first(
                fn (Definition $def) => $word->equals($def->word())
            );
    }
}
