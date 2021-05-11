<?php

namespace App\Collections;

use App\Models\WordRelation;
use Plasticode\Collections\Generic\DbModelCollection;

class WordRelationCollection extends DbModelCollection
{
    protected string $class = WordRelation::class;
}
