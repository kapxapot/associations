<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Collections\StoryCollection;

interface StaticStoryRepositoryInterface
{
    public function getAll(): StoryCollection;
}
