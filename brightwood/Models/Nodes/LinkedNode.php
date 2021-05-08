<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Collections\StoryLinkCollection;
use Brightwood\Models\Data\StoryData;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

abstract class LinkedNode extends StaticNode
{
    abstract public function links(): StoryLinkCollection;

    public function isFinish(?StoryData $data): bool
    {
        return $this->links()->satisfying($data)->isEmpty();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkIntegrity(): void
    {
        parent::checkIntegrity();

        Assert::notEmpty($this->links());
    }
}
