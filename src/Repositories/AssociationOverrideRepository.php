<?php

namespace App\Repositories;

use App\Collections\AssociationOverrideCollection;
use App\Models\Association;
use App\Models\AssociationOverride;
use App\Repositories\Interfaces\AssociationOverrideRepositoryInterface;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Util\SortStep;

class AssociationOverrideRepository extends IdiormRepository implements AssociationOverrideRepositoryInterface
{
    use CreatedRepository;

    protected function getSortOrder(): array
    {
        return [
            SortStep::desc($this->createdAtField)
        ];
    }

    protected function entityClass(): string
    {
        return AssociationOverride::class;
    }

    public function get(?int $id): ?AssociationOverride
    {
        return $this->getEntity($id);
    }

    public function create(array $data): AssociationOverride
    {
        return $this->createEntity($data);
    }

    public function save(AssociationOverride $associationOverride): AssociationOverride
    {
        return $this->saveEntity($associationOverride);
    }

    public function getLatestByAssociation(Association $association): ?AssociationOverride
    {
        return $this->byAssociationQuery($association)->one();
    }

    public function getAllByAssociation(Association $association): AssociationOverrideCollection
    {
        return AssociationOverrideCollection::from(
            $this->byAssociationQuery($association)
        );
    }

    // queries

    protected function byAssociationQuery(Association $association): Query
    {
        return $this
            ->query()
            ->where('association_id', $association->getId());
    }
}
