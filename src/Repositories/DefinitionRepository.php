<?php

namespace App\Repositories;

use App\Models\Definition;
use App\Models\Word;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class DefinitionRepository extends IdiormRepository implements DefinitionRepositoryInterface
{
    protected function entityClass(): string
    {
        return Definition::class;
    }

    public function get(?int $id): ?Definition
    {
        return $this->getEntity($id);
    }

    public function create(array $data): Definition
    {
        return $this->createEntity($data);
    }

    public function save(Definition $definition): Definition
    {
        return $this->saveEntity($definition);
    }

    public function getByWord(Word $word): ?Definition
    {
        return $this
            ->query()
            ->where('word_id', $word->getId())
            ->one();
    }
}
