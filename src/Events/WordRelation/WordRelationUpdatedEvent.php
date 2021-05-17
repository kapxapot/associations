<?php

namespace App\Events\WordRelation;

use App\Models\WordRelation;
use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;

class WordRelationUpdatedEvent extends EntityEvent
{
    protected WordRelation $wordRelation;

    public function __construct(
        WordRelation $wordRelation,
        ?Event $parent = null
    )
    {
        parent::__construct($parent);

        $this->wordRelation = $wordRelation;
    }

    public function getEntity(): WordRelation
    {
        return $this->getWordRelation();
    }

    public function getWordRelation(): WordRelation
    {
        return $this->wordRelation;
    }
}
