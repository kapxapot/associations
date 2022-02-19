<?php

namespace App\Models;

use Plasticode\Models\Generic\DbModel;

/**
 * @property string $name
 * @property integer|null $scopeOverride
 * @property integer $sharingAssociationsDown
 * @property integer $secondary
 * @property string $tag
 * @property integer $weak
 * @property integer $wordForm
 */
class WordRelationType extends DbModel
{
    public function isSharingAssociationsDown(): bool
    {
        return self::toBool($this->sharingAssociationsDown);
    }

    public function isSecondary(): bool
    {
        return self::toBool($this->secondary);
    }

    public function isWeak(): bool
    {
        return self::toBool($this->weak);
    }

    public function isWordForm(): bool
    {
        return self::toBool($this->wordForm);
    }
}
