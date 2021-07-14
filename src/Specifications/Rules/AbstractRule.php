<?php

namespace App\Specifications\Rules;

use Plasticode\Models\Generic\DbModel;
use Plasticode\Util\Classes;

abstract class AbstractRule
{
    abstract public function check(DbModel $model): bool;

    public function getCode(): string
    {
        return Classes::shortName(get_class($this));
    }
}
