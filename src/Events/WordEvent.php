<?php

namespace App\Events;

use App\Models\Word;
use Plasticode\Events\Event;

abstract class WordEvent extends Event
{
    protected Word $word;

    public function __construct(Word $word, Event $parent = null)
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
