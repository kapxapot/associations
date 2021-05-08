<?php

namespace App\Models;

use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\NamedInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property int|null $userId
 * @property string $aliceId
 * @method User|null user()
 * @method static withUser(User|callable|null $user)
 */
class AliceUser extends DbModel implements CreatedAtInterface, NamedInterface, UpdatedAtInterface
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

    public function name(): string
    {
        return 'Алиса ' . $this->getId();
    }
}
