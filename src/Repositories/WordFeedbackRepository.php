<?php

namespace App\Repositories;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Traits\WithWordRepository;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;

class WordFeedbackRepository extends Repository implements WordFeedbackRepositoryInterface
{
    use SearchRepository;
    use WithWordRepository;

    protected function entityClass(): string
    {
        return WordFeedback::class;
    }

    public function get(?int $id): ?WordFeedback
    {
        return $this->getEntity($id);
    }

    public function create(array $data): WordFeedback
    {
        return $this->createEntity($data);
    }

    public function save(WordFeedback $feedback): WordFeedback
    {
        return $this->saveEntity($feedback);
    }

    public function getAllByWord(Word $word): WordFeedbackCollection
    {
        return WordFeedbackCollection::from(
            $this->byWordQuery($word)
        );
    }

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        $query = $query
            ->select($this->getTable() . '.*')
            ->join(
                'words',
                [
                    $this->getTable() . '.word_id',
                    '=',
                    'w.id'
                ],
                'w'
            )
            ->leftOuterJoin(
                'words',
                [
                    $this->getTable() . '.duplicate_id',
                    '=',
                    'duplicate.id'
                ],
                'duplicate'
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
            '(w.word_bin like ? or (duplicate.id is not null and duplicate.word_bin like ?) or typo like ? or user.login like ? or user.name like ?)',
            5
        );
    }
}
