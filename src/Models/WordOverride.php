<?php

namespace App\Models;

use App\Collections\PartOfSpeechCollection;
use App\Semantics\PartOfSpeech;

/**
 * @property string|null $posCorrection Part of speech correction.
 * @property string|null $wordCorrection
 * @property integer $wordId
 * @method Word word()
 * @method static withWord(Word|callable $word)
 */
class WordOverride extends Override
{
    public const POS_DELIMITER = ',';

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'word',
        ];
    }

    public function hasWordCorrection(): bool
    {
        return strlen($this->wordCorrection) > 0;
    }

    public function partsOfSpeech(): ?PartOfSpeechCollection
    {
        if (!$this->hasPosCorrection()) {
            return null;
        }

        $rawParts = explode(self::POS_DELIMITER, $this->posCorrection);

        $parts = array_map(
            fn (string $rp) => PartOfSpeech::getByName($rp),
            $rawParts
        );

        return PartOfSpeechCollection::make($parts);
    }

    public function hasPosCorrection(): bool
    {
        return $this->posCorrection !== null;
    }

    public function isNotEmpty(): bool
    {
        return parent::isNotEmpty()
            || $this->hasWordCorrection()
            || $this->hasPosCorrection();
    }

    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'word' => $this->word()->serialize(),
                'original_word' => $this->word()->originalWord,
                'word_correction' => $this->wordCorrection,
                'pos_correction' => $this->posCorrection,
            ]
        );
    }
}
