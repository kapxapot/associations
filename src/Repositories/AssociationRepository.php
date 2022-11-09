<?php

namespace App\Repositories;

use App\Collections\AssociationCollection;
use App\Collections\WordCollection;
use App\Models\Association;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Data\Query;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;

class AssociationRepository extends LanguageElementRepository implements AssociationRepositoryInterface
{
    use SearchRepository;

    protected function entityClass(): string
    {
        return Association::class;
    }

    protected function collect(ArrayableInterface $arrayable): AssociationCollection
    {
        return AssociationCollection::from($arrayable);
    }

    public function get(?int $id): ?Association
    {
        return $this->getEntity($id);
    }

    public function save(Association $association): Association
    {
        $association->meta = $association->encodeMeta();

        return $this->saveEntity($association);
    }

    public function store(array $data): Association
    {
        return $this->storeEntity($data);
    }

    public function getAllByLanguage(Language $language): AssociationCollection
    {
        return parent::getAllByLanguage($language);
    }

    public function getAllByIds(NumericCollection $ids): AssociationCollection
    {
        return parent::getAllByIds($ids);
    }

    public function getAllByWord(Word $word): AssociationCollection
    {
        $query = $this
            ->query()
            ->whereAnyIs(
                [
                    ['first_word_id' => $word->getId()],
                    ['second_word_id' => $word->getId()],
                ]
            );

        return $this->collect($query);
    }

    public function getAllByWords(WordCollection $words): AssociationCollection
    {
        $ids = $words->ids();

        $query = $this
            ->query()
            ->whereRaw(
                'first_word_id in ? or second_word_id in ?',
                [$ids, $ids]
            );

        return $this->collect($query);
    }

    public function getByOrderedPair(Word $first, Word $second): ?Association
    {
        return $this
            ->query()
            ->where('first_word_id', $first->getId())
            ->where('second_word_id', $second->getId())
            ->one();
    }

    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): AssociationCollection
    {
        return parent::getAllOutOfDate($ttlMin, $limit);
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): AssociationCollection
    {
        return parent::getLastAddedByLanguage($language, $limit);
    }

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        $query = $query
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
            );

        return $this->search(
            $query,
            $filter,
            '(first_word.word_bin like ? or second_word.word_bin like ? or user.login like ? or user.name like ?)',
            4
        );
    }
}
