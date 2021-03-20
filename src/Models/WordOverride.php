<?php

namespace App\Models;

use App\Collections\PartOfSpeechCollection;
use App\Models\Traits\Created;
use App\Semantics\Interfaces\PartOfSpeechableInterface;
use App\Semantics\PartOfSpeech;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;

/**
 * @property integer|null $approved
 * @property integer $disabled
 * @property integer|null $mature
 * @property string|null $posCorrection Part of speech correction.
 * @property string|null $wordCorrection
 * @property integer $wordId
 * @method Word word()
 * @method static withWord(Word|callable $word)
 */
class WordOverride extends DbModel implements CreatedAtInterface, PartOfSpeechableInterface
{
    use Created;

    public const POS_DELIMITER = ',';

    protected function requiredWiths(): array
    {
        return [
            'word',
            $this->creatorPropertyName,
        ];
    }

    public function isApproved(): ?bool
    {
        return $this->hasApproved()
            ? self::toBool($this->approved)
            : null;
    }

    public function hasApproved(): bool
    {
        return $this->approved !== null;
    }

    public function isMature(): ?bool
    {
        return $this->hasMature()
            ? self::toBool($this->mature)
            : null;
    }

    public function hasMature(): bool
    {
        return $this->mature !== null;
    }

    public function hasWordCorrection(): bool
    {
        return strlen($this->wordCorrection) > 0;
    }

    public function partsOfSpeech(): PartOfSpeechCollection
    {
        $collection = PartOfSpeechCollection::empty();

        if (!$this->hasPosCorrection()) {
            return $collection;
        }

        $rawParts = explode(self::POS_DELIMITER, $this->posCorrection);

        $parts = array_map(
            fn (string $rp) => PartOfSpeech::getByName($rp),
            $rawParts
        );

        return $collection->add(...$parts);
    }

    public function hasPosCorrection(): bool
    {
        return strlen($this->posCorrection) > 0;
    }

    public function isDisabled(): bool
    {
        return self::toBool($this->disabled);
    }
}
