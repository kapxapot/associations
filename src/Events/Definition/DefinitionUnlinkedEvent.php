<?php

namespace App\Events\Definition;

use App\Models\Definition;
use App\Models\Word;
use Plasticode\Events\Event;

/**
 * This event is fired when the word definition is unlinked from the word.
 */
class DefinitionUnlinkedEvent extends DefinitionEvent
{
    private Word $unlinkedWord;

    public function __construct(
        Definition $definition,
        Word $unlinkedWord,
        ?Event $parent = null
    )
    {
        parent::__construct($definition, $parent);

        $this->unlinkedWord = $unlinkedWord;
    }

    public function getUnlinkedWord(): Word
    {
        return $this->unlinkedWord;
    }
}
