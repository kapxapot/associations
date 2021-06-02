<?php

namespace App\Models;

use Plasticode\Models\Generic\DbModel;

/**
 * @property integer $disabling
 * @property string $name
 * @property string $tag
 */
class WordRelationType extends DbModel
{
    public function isDisabling(): bool
    {
        return self::toBool($this->disabling);
    }
}
