<?php

namespace App\Repositories;

use App\Collections\WordRelationCollection;
use App\Models\Word;
use App\Models\WordRelation;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Traits\WithWordRepository;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Util\SortStep;

class WordRelationRepository extends IdiormRepository implements WordRelationRepositoryInterface
{
    use CreatedRepository;
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
}
