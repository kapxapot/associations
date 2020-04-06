<?php

namespace App\Repositories;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class WordFeedbackRepository extends IdiormRepository implements WordFeedbackRepositoryInterface
{
    protected string $entityClass = WordFeedback::class;

    public function create(array $data) : WordFeedback
    {
        return $this->createEntity($data);
    }

    public function save(WordFeedback $feedback) : WordFeedback
    {
        return $this->saveEntity($feedback);
    }

    public function getAllByWord(Word $word) : WordFeedbackCollection
    {
        return WordFeedbackCollection::from(
            $this
                ->query()
                ->where('word_id', $word->getId())
        );
    }
}
