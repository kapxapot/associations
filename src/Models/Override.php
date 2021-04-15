<?php

namespace App\Models;

use App\Models\Traits\Created;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;

/**
 * @property integer|null $approved
 * @property integer $disabled
 * @property integer|null $mature
 */
abstract class Override extends DbModel implements CreatedInterface
{
    use Created;

    protected function requiredWiths(): array
    {
        return [
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

    public function isDisabled(): bool
    {
        return self::toBool($this->disabled);
    }

    public function isNotEmpty(): bool
    {
        return $this->hasApproved()
            || $this->hasMature()
            || $this->isDisabled();
    }
}
