<?php

namespace App\Models;

use Plasticode\Models\Generic\DbModel;

/**
 * @property string $name
 * @property integer|null $scopeOverride
 * @property integer $sharingPosDown
 * @property integer $sharingAssociationsDown
 * @property string $tag
 */
class WordRelationType extends DbModel
{
    public function isSharingPosDown(): bool
    {
        return self::toBool($this->sharingPosDown);
    }

    public function isSharingAssociationsDown(): bool
    {
        return self::toBool($this->sharingAssociationsDown);
    }
}
