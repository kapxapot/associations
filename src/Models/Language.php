<?php

namespace App\Models;

use App\Models\Traits\Created;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;

/**
 * @property string|null $code
 * @property string $name
 * @property string|null $yandexDictCode
 */
class Language extends DbModel implements CreatedInterface
{
    use Created;

    const RUSSIAN = 1;

    protected function requiredWiths(): array
    {
        return [
            $this->creatorPropertyName,
        ];
    }

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
        ];
    }
}
