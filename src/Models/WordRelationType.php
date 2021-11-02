<?php

namespace App\Models;

use Plasticode\Models\Generic\DbModel;

/**
 * @property integer $disabling Deprecated, use `$scopeOverride`
 * @property string $name
 * @property integer|null $scopeOverride
 * @property integer $sharingPosDown
 * @property integer $sharingAssociationsDown
 * @property string $tag
 */
class WordRelationType extends DbModel
{
    /**
     * @deprecated
     */
    public function isDisabling(): bool
    {
        return $this->hasScopeOverride();
    }

    public function hasScopeOverride(): bool
    {
        return $this->scopeOverride !== null;
    }

    public function isScopedTo(int $scope): bool
    {
        return $this->scopeOverride == $scope;
    }

    public function isSharingPosDown(): bool
    {
        return self::toBool($this->sharingPosDown);
    }

    public function isSharingAssociationsDown(): bool
    {
        return self::toBool($this->sharingAssociationsDown);
    }
}
