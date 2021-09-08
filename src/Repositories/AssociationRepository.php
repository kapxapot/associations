<?php

namespace App\Repositories;

use App\Collections\AssociationCollection;
use App\Models\Association;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;

class AssociationRepository extends LanguageElementRepository implements AssociationRepositoryInterface
{
    use SearchRepository;

    protected function entityClass(): string
    {
        return Association::class;
    }

    public function get(?int $id): ?Association
    {
        return $this->getEntity($id);
    }

    public function save(Association $association): Association
    {
        return $this->saveEntity($association);
    }

    public function store(array $data): Association
    {
        return $this->storeEntity($data);
    }

    public function getAllByLanguage(Language $language): AssociationCollection
    {
        return AssociationCollection::from(
            parent::getAllByLanguage($language)
        );
    }

    public function getAllByWord(Word $word): AssociationCollection
    {
        return AssociationCollection::from(
            $this
                ->query()
                ->whereAnyIs(
                    [
                        ['first_word_id' => $word->getId()],
                        ['second_word_id' => $word->getId()],
                    ]
                )
        );
    }

    public function getByOrderedPair(Word $first, Word $second): ?Association
    {
        return $this
            ->query()
            ->where('first_word_id', $first->getId())
            ->where('second_word_id', $second->getId())
            ->one();
    }

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): AssociationCollection
    {
        return AssociationCollection::from(
            parent::getAllOutOfDate($ttlMin, $limit)
        );
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): AssociationCollection
    {
        return AssociationCollection::from(
            parent::getLastAddedByLanguage($language, $limit)
        );
    }

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        return $query
            ->select($this->getTable() . '.*')
            ->join(
                'words',
                [
                    $this->getTable() . '.first_word_id',
                    '=',
                    'first_word.id'
                ],
                'first_word'
            )
            ->join(
                'words',
                [
                    $this->getTable() . '.second_word_id',
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
            )
            ->search(
                mb_strtolower($filter),
                '(first_word.word_bin like ? or second_word.word_bin like ? or user.login like ? or user.name like ?)',
                4
            );
    }
}
