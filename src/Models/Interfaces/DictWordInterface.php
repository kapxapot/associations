<?php

namespace App\Models\Interfaces;

interface DictWordInterface
{
    function isNoun() : bool;
    function isValid() : bool;
    function partOfSpeech() : string;
}
