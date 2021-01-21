<?php

namespace App\Mapping\Providers;

use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Psr\Container\ContainerInterface;

class ParsingProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            TagLinkMapper::class =>
                fn (ContainerInterface $c) => new TagLinkMapper(
                    $c->get(Core\RendererInterface::class),
                    $c->get(Core\LinkerInterface::class)
                ),

            PageLinkMapper::class =>
                fn (ContainerInterface $c) => new PageLinkMapper(
                    $c->get(CoreRepositories\PageRepositoryInterface::class),
                    $c->get(CoreRepositories\TagRepositoryInterface::class),
                    $c->get(Core\RendererInterface::class),
                    $c->get(Core\LinkerInterface::class),
                    $c->get(TagLinkMapper::class)
                ),

            NewsLinkMapper::class =>
                fn (ContainerInterface $c) => new NewsLinkMapper(
                    $c->get(Core\RendererInterface::class),
                    $c->get(Core\LinkerInterface::class)
                ),

            DoubleBracketsConfig::class =>
                fn (ContainerInterface $c) => (new DoubleBracketsConfig())
                    ->setDefaultMapper(
                        $c->get(PageLinkMapper::class)
                    )
                    ->registerTaggedMappers(
                        $c->get(NewsLinkMapper::class),
                        $c->get(TagLinkMapper::class)
                    ),
        ];
    }
}
