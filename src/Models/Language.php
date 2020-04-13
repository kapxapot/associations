<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;

/**
 * @property int $id
 * @property string $name
 * @property string|null $yandexDictWord
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
