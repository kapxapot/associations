<?php

namespace App\Collections;

use App\Models\DTO\MetaAssociation;
use Plasticode\Collections\Generic\TypedCollection;

class MetaAssociationCollection extends TypedCollection
{
    protected string $class = MetaAssociation::class;
}
