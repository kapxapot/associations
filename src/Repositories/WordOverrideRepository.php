<?php

namespace App\Repositories;

use App\Collections\WordOverrideCollection;
use App\Models\Word;
use App\Models\WordOverride;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Util\SortStep;

class WordOverrideRepository extends IdiormRepository implements WordOverrideRepositoryInterface
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

    // queries

    protected function byWordQuery(Word $word): Query
    {
        return $this
            ->query()
            ->where('word_id', $word->getId());
    }
}
