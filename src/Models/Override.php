<?php

namespace App\Models;

use App\Models\Traits\Created;
use App\Semantics\Scope;
use App\Semantics\Severity;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;

/**
 * @property integer|null $scope
 * @property integer|null $severity
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

    public function isPublic(): bool
    {
        return $this->scope == Scope::PUBLIC;
    }

    public function isDisabled(): bool
    {
        return $this->scope == Scope::DISABLED;
    }

    public function isMature(): bool
    {
        return $this->severity == Severity::MATURE;
    }

    public function hasScope(): bool
    {
        return $this->scope !== null;
    }

    public function hasSeverity(): bool
    {
        return $this->severity !== null;
    }

    public function isNotEmpty(): bool
    {
        return $this->hasScope() || $this->hasSeverity();
    }

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'scope' => $this->scope,
            'severity' => $this->severity,
            'creator' => $this->creator()->serialize(),
            'created_at' => $this->createdAtIso(),
        ];
    }
}
