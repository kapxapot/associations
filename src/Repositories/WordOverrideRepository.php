<?php

namespace App\Repositories;

use App\Collections\WordOverrideCollection;
use App\Models\Word;
use App\Models\WordOverride;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Traits\WithWordRepository;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;
use Plasticode\Util\SortStep;

class WordOverrideRepository extends IdiormRepository implements FilteringRepositoryInterface, WordOverrideRepositoryInterface
{
    use CreatedRepository;
    use SearchRepository;
    use WithWordRepository;

    protected function getSortOrder(): array
    {
        return [
            SortStep::desc($this->createdAtField)
        ];
    }

    protected function entityClass(): string
    {
        return WordOverride::class;
    }

    public function get(?int $id): ?WordOverride
    {
        return $this->getEntity($id);
    }

    public function create(array $data): WordOverride
    {
        return $this->createEntity($data);
    }

    public function save(WordOverride $wordOverride): WordOverride
    {
        return $this->saveEntity($wordOverride);
    }

    public function getLatestByWord(Word $word): ?WordOverride
    {
        return $this->byWordQuery($word)->one();
    }

    public function getAllByWord(Word $word): WordOverrideCollection
    {
        return WordOverrideCollection::from(
            $this->byWordQuery($word)
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
                    $this->getTable() . '.word_id',
                    '=',
                    'word.id'
                ],
                'word'
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
                '(word_correction like ? or word.original_word like ? or user.login like ? or user.name like ?)',
                4
            );
    }
}
