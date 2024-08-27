<?php

namespace Brightwood\Collections;

use Brightwood\Models\Language;
use Plasticode\Collections\Generic\TypedCollection;

class LanguageCollection extends TypedCollection
{
    protected string $class = Language::class;
}
