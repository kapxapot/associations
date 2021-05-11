<?php

namespace App\Repositories;

use App\Models\Definition;
use App\Models\Word;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use App\Repositories\Traits\WithWordRepository;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class DefinitionRepository extends IdiormRepository implements DefinitionRepositoryInterface
{
    use WithWordRepository;

    protected function entityClass(): string
    {
        return Definition::class;
    }

    public function get(?int $id): ?Definition
    {
        return $this->getEntity($id);
    }

    public function save(Definition $definition): Definition
    {
        return $this->saveEntity($definition);
    }

    public function store(array $data): Definition
    {
        return $this->storeEntity($data);
    }

    public function delete(Definition $definition): bool
    {
        return $this->deleteEntity($definition);
    }

    public function getByWord(Word $word): ?Definition
    {
        return $this->byWordQuery($word)->one();
    }
}
