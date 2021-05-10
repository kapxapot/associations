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

    public function isPrimary(): bool
    {
        return self::toBool($this->primary);
    }
}
