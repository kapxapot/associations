<?php

namespace App\Repositories;

use App\Collections\AssociationOverrideCollection;
use App\Models\Association;
use App\Models\AssociationOverride;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\AssociationOverrideRepositoryInterface;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;
use Plasticode\Util\SortStep;

class AssociationOverrideRepository extends Repository implements AssociationOverrideRepositoryInterface
{
    use CreatedRepository;
    use SearchRepository;

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

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        $query = $query
            ->select($this->getTable() . '.*')
            ->join(
                'associations',
                [
                    $this->getTable() . '.association_id',
                    '=',
                    'ass.id'
                ],
                'ass'
            )
            ->join(
                'words',
                [
                    'ass.first_word_id',
                    '=',
                    'first_word.id'
                ],
                'first_word'
            )
            ->join(
                'words',
                [
                    'ass.second_word_id',
                    '=',
                    'second_word.id'
                ],
                'second_word'
            )
            ->join(
                'users',
                [
                    $this->getTable() . '.created_by',
                    '=',
                    'user.id'
                ],
                'user'
            );

        return $this->search(
            $query,
            $filter,
            '(first_word.word_bin like ? or second_word.word_bin like ? or user.login like ? or user.name like ?)',
            4
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
