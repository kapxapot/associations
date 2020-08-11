<?php

namespace App\Repositories\Brightwood\Interfaces;

use App\Models\Brightwood\Stories\Story;

interface StoryRepositoryInterface
{
    function get(?int $id) : ?Story;
}
