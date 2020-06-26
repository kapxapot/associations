<?php

namespace App\Collections;

use App\Models\Page;
use Plasticode\Collections\PageCollection as BasePageCollection;

class PageCollection extends BasePageCollection
{
    protected string $class = Page::class;
}
