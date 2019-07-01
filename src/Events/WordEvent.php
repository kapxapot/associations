<?php

namespace App\Events;

use Plasticode\Events\Event;

use App\Models\Word;

abstract class WordEvent extends Event
{
    private $word;

    public function __construct(Word $word)
    {
        $this->word = $word;
    }

    public function getWord() : Word
    {
        return $this->word;
    }
}
