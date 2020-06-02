<?php

namespace App\Models\Interfaces;

use App\Models\Language;
use App\Models\Word;
use Plasticode\Models\Interfaces\DbModelInterface;

interface DictWordInterface extends DbModelInterface
{
    function getLanguage() : Language;

    /**
     * Returns word string.
     */
    function getWord() : string;

    /**
     * Returns linked {@see Word}.
     */
    function getLinkedWord() : ?Word;

    /**
     * @return static
     */
    function linkWord(Word $word) : self;

    /**
     * @return static
     */
    function unlinkWord() : self;

    function isNoun() : bool;
    function isValid() : bool;
    function partOfSpeech() : string;
}
