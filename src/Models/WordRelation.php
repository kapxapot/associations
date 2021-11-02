<?php

namespace App\Models;

use App\Models\Traits\Stamps;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;

/**
 * @property integer $mainWordId
 * @property integer $primary
 * @property integer $typeId
 * @property integer $wordId
 * @method Word mainWord()
 * @method WordRelationType type()
 * @method Word word()
 * @method static withMainWord(Word|callable $mainWord)
 * @method static withType(WordRelationType|callable $type)
 * @method static withWord(Word|callable $word)
 */
class WordRelation extends DbModel implements CreatedInterface, UpdatedAtInterface
{
    use Stamps;

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'mainWord',
            'type',
            'word',
        ];
    }

    /**
     * Type has scope override that equals to `$scope`.
     */
    public function isScopedTo(int $scope): bool
    {
        return $this->type()->isScopedTo($scope);
    }

    public function isSharingPosDown(): bool
    {
        return $this->type()->isSharingPosDown();
    }

    public function isPrimary(): bool
    {
        return self::toBool($this->primary);
    }

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'word' => $this->word()->serialize(),
            'main_word' => $this->mainWord()->serialize(),
            'type' => $this->type()->serialize(),
            'primary' => $this->primary,
            'creator' => $this->creator()->serialize(),
            'created_at' => $this->createdAtIso(),
        ];
    }
}
