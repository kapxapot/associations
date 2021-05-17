<?php

namespace App\Collections;

use App\Models\WordRelationType;
use Plasticode\Collections\Generic\DbModelCollection;

class WordRelationTypeCollection extends DbModelCollection
{
    protected string $class = WordRelationType::class;
}
