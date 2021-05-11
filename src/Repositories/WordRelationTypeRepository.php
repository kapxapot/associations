<?php

namespace App\Repositories;

use App\Models\WordRelationType;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class WordRelationTypeRepository extends IdiormRepository implements WordRelationTypeRepositoryInterface
{
    protected function entityClass(): string
    {
        return WordRelationType::class;
    }

    public function get(?int $id): ?WordRelationType
    {
        return $this->getEntity($id);
    }
}
