<?php

namespace App\Config\Factories;

use App\Models\News;
use App\Models\Page;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Config\TagsConfig;

class TagsConfigFactory
{
    public function __invoke(): TagsConfigInterface
    {
        return new TagsConfig([
            News::class => 'news',
            Page::class => 'pages',
        ]);
    }
}
