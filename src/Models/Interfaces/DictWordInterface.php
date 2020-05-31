<?php

namespace App\Models\Interfaces;

use App\Models\Language;
use Plasticode\Models\Interfaces\DbModelInterface;

/**
 * @property integer|null $wordId
 */
interface DictWordInterface extends DbModelInterface
{
    function getLanguage() : Language;

    /**
     * Returns word string.
     */
    function getWord() : string;

    function isNoun() : bool;
    function isValid() : bool;
    function partOfSpeech() : string;
}
