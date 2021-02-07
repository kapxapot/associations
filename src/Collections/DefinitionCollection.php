<?php

namespace App\Collections;

use App\Models\Definition;
use Plasticode\Collections\Generic\DbModelCollection;

class DefinitionCollection extends DbModelCollection
{
    protected string $class = Definition::class;
}
