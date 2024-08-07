<?php

namespace Brightwood\Repositories\Interfaces;

use App\Models\User;
use Brightwood\Models\StoryCandidate;
use Plasticode\Repositories\Interfaces\Generic\RepositoryInterface;

interface StoryCandidateRepositoryInterface extends RepositoryInterface
{
    public function getByCreator(User $user): ?StoryCandidate;

    public function save(StoryCandidate $candidate): StoryCandidate;

    public function delete(StoryCandidate $candidate): bool;
}
