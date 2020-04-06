<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property int $id
 * @property string $name
 */
class Language extends DbModel
{
    const RUSSIAN = 1;

    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
        ];
    }
}
