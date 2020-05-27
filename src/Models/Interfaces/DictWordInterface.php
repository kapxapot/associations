<?php

namespace App\Models\Interfaces;

use App\Models\Language;
use Plasticode\Models\Interfaces\DbModelInterface;

interface DictWordInterface extends DbModelInterface
{
    function getLanguage() : Language;
    function getWord() : string;
    function isNoun() : bool;
    function isValid() : bool;
    function partOfSpeech() : string;
}
