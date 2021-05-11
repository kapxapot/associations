<?php

namespace App\Repositories;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Traits\WithWordRepository;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class WordFeedbackRepository extends IdiormRepository implements WordFeedbackRepositoryInterface
{
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
}
