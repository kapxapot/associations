<?php

namespace App\Models\Interfaces;

use App\Models\Language;
use App\Models\Word;
use App\Semantics\Interfaces\PartOfSpeechableInterface;
use App\Semantics\PartOfSpeech;
use Plasticode\Models\Interfaces\DbModelInterface;

interface DictWordInterface extends DbModelInterface, PartOfSpeechableInterface
{
    public function getLanguage(): Language;

    /**
     * Returns word string.
     */
    public function getWord(): string;

    /**
     * Checks if the dict word matches the word (language and word are equal).
     */
    public function matchesWord(Word $word): bool;

    /**
     * Returns linked {@see Word}.
     */
    public function getLinkedWord(): ?Word;

    /**
     * @return $this
     */
    public function linkWord(Word $word): self;

    /**
     * @return $this
     */
    public function unlinkWord(): self;

    public function partOfSpeech(): ?PartOfSpeech;

    public function isValid(): bool;

    public function isGood(): bool;

    public function isBad(): bool;

    public function isUgly(): bool;
}
