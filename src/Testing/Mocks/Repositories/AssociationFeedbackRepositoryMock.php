<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Models\AssociationFeedback;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Search\SearchParams;
use Plasticode\Search\SearchResult;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;

class AssociationFeedbackRepositoryMock extends RepositoryMock implements AssociationFeedbackRepositoryInterface
{
    /** @var HydratorInterface|ObjectProxy */
    private $hydrator;

    private AssociationFeedbackCollection $feedbacks;

    /**
     * @param HydratorInterface|ObjectProxy $hydrator
     */
    public function __construct(
        $hydrator
    )
    {
        $this->hydrator = $hydrator;

        $this->feedbacks = AssociationFeedbackCollection::empty();
    }

    public function get(?int $id) : ?AssociationFeedback
    {
        return $this->feedbacks->first(
            fn (AssociationFeedback $f) => $f->getId() == $id
        );
    }

    public function store(array $data) : AssociationFeedback
    {
        return $this->hydrator->hydrate(
            AssociationFeedback::create($data)
        );
    }

    public function save(AssociationFeedback $feedback) : AssociationFeedback
    {
        if (!$this->feedbacks->contains($feedback)) {
            if (!$feedback->isPersisted()) {
                $feedback->id = $this->feedbacks->nextId();
            }

            $this->feedbacks = $this->feedbacks->add($feedback);
        }

        return $this->hydrator->hydrate($feedback);
    }

    public function getAllByAssociation(
        Association $association
    ) : AssociationFeedbackCollection
    {
        return $this
            ->feedbacks
            ->where(
                fn (AssociationFeedback $f) => $f->association()->equals($association)
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
