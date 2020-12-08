<?php

namespace App\Collections;

use App\Models\MetaAssociation;
use Plasticode\Collections\Basic\TypedCollection;

class MetaAssociationCollection extends TypedCollection
{
    protected string $class = MetaAssociation::class;
}
