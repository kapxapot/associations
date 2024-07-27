<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface StoryRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?Story;

    public function getByUuid(string $uuid): ?Story;

    public function getAll(): StoryCollection;
}
