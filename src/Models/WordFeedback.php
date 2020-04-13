<?php

namespace App\Models;

/**
 * @property integer $wordId
 * @property string|null $typo
 * @property integer|null $duplicateId
 * @method Word|null duplicate()
 * @method Word word()
 * @method self withDuplicate(Word|callable|null $duplicate)
 * @method self withWord(Word|callable $word)
 */
class WordFeedback extends Feedback
{
    protected function requiredWiths(): array
    {
        return [...parent::requiredWiths(), 'duplicate', 'word'];
    }

    public function hasTypo() : bool
    {
        return strlen($this->typo) > 0;
    }

    public function hasDuplicate() : bool
    {
        return $this->duplicate() !== null;
    }
}
