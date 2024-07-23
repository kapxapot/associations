<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\StoryVersion;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface StoryVersionRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?StoryVersion;

    public function getCurrentVersion(Story $story): ?StoryVersion;
}
