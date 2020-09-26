<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Collections\StoryLinkCollection;
use Webmozart\Assert\Assert;

abstract class LinkedNode extends TextNode
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
