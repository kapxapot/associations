<?php

namespace App\Events\Word;

use App\Models\Word;
use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;

abstract class WordEvent extends EntityEvent
{
    protected Word $word;

    public function __construct(Word $word, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->word = $word;
    }

    public function getWord() : Word
    {
        return $this->word;
    }

    public function getEntity() : Word
    {
        return $this->getWord();
    }
}
