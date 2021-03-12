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

    public function hasTypo(): bool
    {
        return strlen($this->typo) > 0;
    }

    public function hasDuplicate(): bool
    {
        return $this->duplicateId > 0;
    }

    /**
     * For unknown reason Twig can't read 'duplicate' fake method
     * (while having no problems with 'duplicat' or 'duplicatee').
     * 
     * The reason for that behavior is not found, so this explicit
     * function declaration is a temporary (or a permanent) solution.
     */
    public function duplicate(): ?Word
    {
        return $this->getWithProperty(
            $this->duplicatePropertyName
        );
    }
}
