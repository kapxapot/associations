<?php

namespace App\Collections;

use App\Models\News;

class NewsCollection extends NewsSourceCollection
{
    protected string $class = News::class;
}
