<?php

namespace App\Models\Interfaces;

use App\Models\Language;
use App\Models\Word;
use App\Semantics\Interfaces\PartOfSpeechableInterface;
use App\Semantics\PartOfSpeech;
use Plasticode\Models\Interfaces\DbModelInterface;

interface DictWordInterface extends DbModelInterface, PartOfSpeechableInterface
{
    function getLanguage(): Language;

    /**
     * Returns word string.
     */
    function getWord(): string;

    /**
     * Checks if the dict word matches the word (language and word are equal).
     */
    function matchesWord(Word $word): bool;

    /**
     * Returns linked {@see Word}.
     */
    function getLinkedWord(): ?Word;

    /**
     * @return static
     */
    function linkWord(Word $word): self;

    /**
     * @return static
     */
    function unlinkWord(): self;

    function partOfSpeech(): ?PartOfSpeech;

    function isValid(): bool;

    function isGood(): bool;

    function isBad(): bool;

    function isUgly(): bool;
}
