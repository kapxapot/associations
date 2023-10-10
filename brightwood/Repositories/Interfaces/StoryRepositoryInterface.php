<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;

interface StoryRepositoryInterface
{
    public function get(?int $id): ?Story;

    public function getAllPublished(): StoryCollection;
}
