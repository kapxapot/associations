<?php

namespace App\Models;

use App\Models\Traits\Created;
use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;

/**
 * @property string|null $code
 * @property string $name
 * @property string|null $yandexDictCode
 */
class Language extends DbModel implements CreatedAtInterface
{
    use Created;

    const RUSSIAN = 1;

    protected function requiredWiths() : array
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
