<?php

namespace App\Events\Override;

use App\Models\WordOverride;
use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;

class WordOverrideCreatedEvent extends EntityEvent
{
    protected WordOverride $wordOverride;

    public function __construct(
        WordOverride $wordOverride,
        ?Event $parent = null
    )
    {
        parent::__construct($parent);

        $this->wordOverride = $wordOverride;
    }

    public function getEntity(): WordOverride
    {
        return $this->getWordOverride();
    }

    public function getWordOverride(): WordOverride
    {
        return $this->wordOverride;
    }
}
