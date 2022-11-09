<?php

namespace App\Repositories;

use App\Collections\WordRelationTypeCollection;
use App\Models\WordRelationType;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;

class WordRelationTypeRepository extends Repository implements WordRelationTypeRepositoryInterface
{
    protected function entityClass(): string
    {
        return WordRelationType::class;
    }

    public function get(?int $id): ?WordRelationType
    {
        return $this->getEntity($id);
    }

    public function getAll(): WordRelationTypeCollection
    {
        return WordRelationTypeCollection::from(
            $this->query()
        );
    }
}
