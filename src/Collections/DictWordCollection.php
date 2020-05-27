<?php

namespace App\Collections;

use App\Models\Interfaces\DictWordInterface;
use Plasticode\Collections\Basic\DbModelCollection;

class DictWordCollection extends DbModelCollection
{
    protected string $class = DictWordInterface::class;
}
