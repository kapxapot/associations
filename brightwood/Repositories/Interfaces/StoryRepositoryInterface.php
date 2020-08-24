<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Models\Stories\Story;

interface StoryRepositoryInterface
{
    function get(?int $id) : ?Story;
}
