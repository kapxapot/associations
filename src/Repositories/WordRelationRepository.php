<?php

namespace App\Repositories;

use App\Collections\WordRelationCollection;
use App\Models\Word;
use App\Models\WordRelation;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Traits\WithWordRepository;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;
use Plasticode\Util\SortStep;

class WordRelationRepository extends IdiormRepository implements WordRelationRepositoryInterface
{
    use CreatedRepository;
    use SearchRepository;
    use WithWordRepository;

    protected function getSortOrder(): array
    {
        return [
            SortStep::asc($this->createdAtField)
        ];
    }

    protected function entityClass(): string
    {
        return WordRelation::class;
    }

    public function get(?int $id): ?WordRelation
    {
        return $this->getEntity($id);
    }

    public function create(array $data): WordRelation
    {
        return $this->createEntity($data);
    }

    public function save(WordRelation $wordRelation): WordRelation
    {
        return $this->saveEntity($wordRelation);
    }

    public function getAllByWord(Word $word): WordRelationCollection
    {
        return WordRelationCollection::from(
            $this->byWordQuery($word)
        );
    }

    public function getAllByMainWord(Word $mainWord): WordRelationCollection
    {
        return WordRelationCollection::from(
            $this->query()->where('main_word_id', $mainWord->getId())
        );
    }

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        return $query
            ->select($this->getTable() . '.*')
            ->join(
                'word_relation_types',
                [
                    $this->getTable() . '.type_id',
                    '=',
                    'type.id'
                ],
                'type'
            )
            ->join(
                'words',
                [
                    $this->getTable() . '.word_id',
                    '=',
                    'word.id'
                ],
                'word'
            )
            ->join(
                'words',
                [
                    $this->getTable() . '.main_word_id',
                    '=',
                    'main_word.id'
                ],
                'main_word'
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
                '(type.tag like ? or word.word_bin like ? or main_word.word_bin like ? or user.login like ? or user.name like ?)',
                5
            );
    }
}
