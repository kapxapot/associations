<?php

namespace App\Collections;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collections\NewsSourceCollection as BaseNewsSourceCollection;

class NewsSourceCollection extends BaseNewsSourceCollection
{
    protected string $class = NewsSourceInterface::class;
}
