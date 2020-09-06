<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Story;

interface StoryRepositoryInterface
{
    function get(?int $id) : ?Story;
    function getAllPublished() : StoryCollection;
}
