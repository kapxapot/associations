<?php

namespace App\Repositories\Brightwood\Interfaces;

use App\Models\Brightwood\Story;

interface StoryRepositoryInterface
{
    function get(?int $id) : ?Story;
}
