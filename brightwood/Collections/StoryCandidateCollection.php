<?php

namespace Brightwood\Collections;

use Brightwood\Models\StoryCandidate;
use Plasticode\Collections\Generic\DbModelCollection;

class StoryCandidateCollection extends DbModelCollection
{
    protected string $class = StoryCandidate::class;
}
