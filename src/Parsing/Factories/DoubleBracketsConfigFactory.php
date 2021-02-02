<?php

namespace App\Parsing\Factories;

use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;

class DoubleBracketsConfigFactory
{
    public function __invoke(
        NewsLinkMapper $newsLinkMapper,
        PageLinkMapper $pageLinkMapper,
        TagLinkMapper $tagLinkMapper
    ): DoubleBracketsConfig
    {
        $config = new DoubleBracketsConfig();

        return $config
            ->setDefaultMapper($pageLinkMapper)
            ->registerTaggedMappers(
                $newsLinkMapper,
                $tagLinkMapper
            );
    }
}
