<?php

namespace App\Repositories\Traits;

use App\Models\Word;
use Plasticode\Data\Query;

trait WithWordRepository
{
    protected string $wordIdField = 'word_id';

    abstract protected function query(): Query;

    public function getCountByWord(Word $word): int
    {
        return $this
            ->byWordQuery($word)
            ->count();
    }

    protected function byWordQuery(Word $word): Query
    {
        return $this->filterByWord($this->query(), $word);
    }

    protected function filterByWord(Query $query, Word $word): Query
    {
        return $query->where($this->wordIdField, $word->getId());
    }
}
