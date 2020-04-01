<?php

namespace App\Models;

use Webmozart\Assert\Assert;

/**
 * @property integer $wordId
 * @property string|null $typo
 * @property integer|null $duplicateId
 */
class WordFeedback extends Feedback
{
    protected ?Word $word = null;
    protected ?Word $duplicate = null;

    private bool $wordInitialized = false;
    private bool $duplicateInitialized = false;

    public function word() : Word
    {
        Assert::true($this->wordInitialized);

        return $this->word;
    }

    public function withWord(Word $word) : self
    {
        $this->word = $word;
        $this->wordInitialized = true;

        return $this;
    }

    public function hasTypo() : bool
    {
        return strlen($this->typo) > 0;
    }

    public function duplicate() : ?Word
    {
        Assert::true($this->duplicateInitialized);

        return $this->duplicate;
    }

    public function withDuplicate(?Word $duplicate) : self
    {
        $this->duplicate = $duplicate;
        $this->duplicateInitialized = true;

        return $this;
    }

    public function hasDuplicate() : bool
    {
        return $this->duplicate() !== null;
    }
}
