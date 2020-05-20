<?php

namespace App\Models;

use App\Models\Traits\Created;
use Plasticode\Models\DbModel;

/**
 * @property string $name
 * @property string|null $yandexDictCode
 */
class Language extends DbModel
{
    use Created;

    const RUSSIAN = 1;

    protected function requiredWiths(): array
    {
        return ['creator'];
    }

    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
        ];
    }
}
