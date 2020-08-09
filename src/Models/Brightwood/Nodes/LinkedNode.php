<?php

namespace App\Models\Brightwood\Nodes;

use App\Collections\Brightwood\StoryLinkCollection;
use Webmozart\Assert\Assert;

abstract class LinkedNode extends StoryNode
{
    abstract public function links() : StoryLinkCollection;

    public function isFinish() : bool
    {
        return $this->links()->isEmpty();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkIntegrity() : void
    {
        parent::checkIntegrity();

        Assert::notEmpty($this->links());
    }
}
