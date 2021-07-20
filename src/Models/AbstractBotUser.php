<?php

namespace App\Models;

use App\Models\Interfaces\NamedInterface;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property integer|null $userId
 * @method User|null user()
 * @method static withUser(User|callable|null $user)
 */
abstract class AbstractBotUser extends DbModel implements CreatedAtInterface, NamedInterface, UpdatedAtInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['user'];
    }

    public function isValid(): bool
    {
        return $this->user() !== null;
    }

    public function isNew(): bool
    {
        return $this->isValid() && $this->user()->lastGame() === null;
    }

    // NamedInterface

    abstract public function name(): string;
}
