<?php

namespace App\Events\DictWord;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Word;
use Plasticode\Events\Event;

/**
 * This event is fired when the dict word is unlinked from its word.
 * 
 * Technically this means that the dict word and (no longer) its word
 * are not related anymore (the word of dict word is changed from not-null to null).
 */
class DictWordUnlinkedEvent extends DictWordEvent
{
    private Word $unlinkedWord;

    public function __construct(
        DictWordInterface $dictWord,
        Word $unlinkedWord,
        ?Event $parent = null
    )
    {
        parent::__construct($dictWord, $parent);

        $this->unlinkedWord = $unlinkedWord;
    }

    public function getUnlinkedWord() : Word
    {
        return $this->unlinkedWord;
    }
}
