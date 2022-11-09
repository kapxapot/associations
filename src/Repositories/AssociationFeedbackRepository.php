<?php

namespace App\Repositories;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Models\AssociationFeedback;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;

class AssociationFeedbackRepository extends Repository implements AssociationFeedbackRepositoryInterface, FilteringRepositoryInterface
{
    use SearchRepository;

    protected function entityClass(): string
    {
        return AssociationFeedback::class;
    }

    public function get(?int $id): ?AssociationFeedback
    {
        return $this->getEntity($id);
    }

    public function create(array $data): AssociationFeedback
    {
        return $this->createEntity($data);
    }

    public function save(AssociationFeedback $feedback): AssociationFeedback
    {
        return $this->saveEntity($feedback);
    }

    public function getAllByAssociation(
        Association $association
    ): AssociationFeedbackCollection
    {
        return AssociationFeedbackCollection::from(
            $this
                ->query()
                ->where('association_id', $association->getId())
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
}
