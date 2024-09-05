<?php

namespace Brightwood\Repositories;

use App\Models\User;
use Brightwood\Models\StoryCandidate;
use Brightwood\Repositories\Interfaces\StoryCandidateRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;
use Plasticode\Util\Date;

class StoryCandidateRepository extends IdiormRepository implements StoryCandidateRepositoryInterface
{
    use CreatedRepository;

    protected function entityClass(): string
    {
        return StoryCandidate::class;
    }

    public function getByCreator(User $user): ?StoryCandidate
    {
        return $this
            ->filterByCreator($this->query(), $user)
            ->one();
    }

    public function save(StoryCandidate $candidate): StoryCandidate
    {
        if ($candidate->isPersisted()) {
            $candidate->updatedAt = Date::dbNow();
        }

        return $this->saveEntity($candidate);
    }

    public function delete(StoryCandidate $candidate): bool
    {
        return $this->deleteEntity($candidate);
    }
}
