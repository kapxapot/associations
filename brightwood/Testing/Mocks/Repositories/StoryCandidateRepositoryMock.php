<?php

namespace Brightwood\Testing\Mocks\Repositories;

use App\Models\User;
use Brightwood\Collections\StoryCandidateCollection;
use Brightwood\Models\StoryCandidate;
use Brightwood\Repositories\Interfaces\StoryCandidateRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;

class StoryCandidateRepositoryMock extends RepositoryMock implements StoryCandidateRepositoryInterface
{
    /** @var HydratorInterface|ObjectProxy */
    private $hydrator;

    private StoryCandidateCollection $candidates;

    /**
     * @param HydratorInterface|ObjectProxy $hydrator
     */
    public function __construct($hydrator)
    {
        $this->hydrator = $hydrator;
        $this->candidates = StoryCandidateCollection::empty();
    }

    public function getByCreator(User $user): ?StoryCandidate
    {
        return $this->candidates->first(
            fn (StoryCandidate $c) => $c->creator()->equals($user)
        );
    }

    public function save(StoryCandidate $candidate): StoryCandidate
    {
        if ($this->candidates->contains($candidate)) {
            return $candidate;
        }

        if (!$candidate->isPersisted()) {
            $candidate->id = $this->candidates->nextId();
        }

        $this->candidates = $this->candidates->add($candidate);

        return $this->hydrator->hydrate($candidate);
    }

    public function delete(StoryCandidate $candidate): bool
    {
        $this->candidates = $this->candidates->except($candidate);
        return true;
    }
}
