<?php

namespace App\Events;

use App\Models\Word;
use Plasticode\Events\Event;
use Plasticode\Models\DbModel;

abstract class WordEvent extends Event
{
    private $word;

    public function __construct(Word $word, Event $parent = null)
    {
        parent::__construct($parent);

        $this->word = $word;
    }

    public function getWord() : Word
    {
        return $this->word;
    }

    public function getEntity() : DbModel
    {
        return $this->getWord();
    }
}
