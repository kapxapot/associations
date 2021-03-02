<?php

namespace App\Semantics\Interfaces;

use App\Collections\PartOfSpeechCollection;

interface PartOfSpeechableInterface
{
    public function partsOfSpeech(): PartOfSpeechCollection;
}
