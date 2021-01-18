<?php

namespace App\Collections;

use App\Models\Interfaces\DictWordInterface;
use Plasticode\Collections\Generic\DbModelCollection;

class DictWordCollection extends DbModelCollection
{
    protected string $class = DictWordInterface::class;
}
