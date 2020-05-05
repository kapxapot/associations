<?php

namespace App\Models;

/**
 * @property integer|null $duplicateId
 * @property string|null $typo
 * @property integer $wordId
 * @method Word word()
 * @method static withDuplicate(Word|callable|null $duplicate)
 * @method static withWord(Word|callable $word)
 */
class WordFeedback extends Feedback
{
    private string $duplicatePropertyName = 'duplicate';

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            $this->duplicatePropertyName,
            'word',
        ];
    }

    public function hasTypo() : bool
    {
        return strlen($this->typo) > 0;
    }

    public function hasDuplicate() : bool
    {
        return $this->duplicateId > 0;
    }

    public function duplicate() : ?Word
    {
        return $this->getWithProperty(
            $this->duplicatePropertyName
        );
    }
}
