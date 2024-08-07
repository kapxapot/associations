<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Search\SearchParams;
use Plasticode\Search\SearchResult;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;

class WordFeedbackRepositoryMock extends RepositoryMock implements WordFeedbackRepositoryInterface
{
    /** @var HydratorInterface|ObjectProxy */
    private $hydrator;

    private WordFeedbackCollection $feedbacks;

    /**
     * @param HydratorInterface|ObjectProxy $hydrator
     */
    public function __construct(
        $hydrator
    )
    {
        $this->hydrator = $hydrator;

        $this->feedbacks = WordFeedbackCollection::empty();
    }

    public function get(?int $id) : ?WordFeedback
    {
        return $this->feedbacks->first(
            fn (WordFeedback $f) => $f->getId() == $id
        );
    }

    public function store(array $data): WordFeedback
    {
        return $this->hydrator->hydrate(
            WordFeedback::create($data)
        );
    }

    public function save(WordFeedback $feedback) : WordFeedback
    {
        if (!$this->feedbacks->contains($feedback)) {
            if (!$feedback->isPersisted()) {
                $feedback->id = $this->feedbacks->nextId();
            }

            $this->feedbacks = $this->feedbacks->add($feedback);
        }

        return $this->hydrator->hydrate($feedback);
    }

    public function getAllByWord(Word $word) : WordFeedbackCollection
    {
        return $this
            ->feedbacks
            ->where(
                fn (WordFeedback $f) => $f->word()->equals($word)
            );
    }

    public function getSearchResult(SearchParams $searchParams): SearchResult
    {
        // placeholder
        return new SearchResult(
            $this->feedbacks,
            $this->feedbacks->count(),
            $this->feedbacks->count()
        );
    }
}
