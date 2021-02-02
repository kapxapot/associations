<?php

namespace App\Tests\Mapping;

use App\Mapping\Providers\GeneralProvider;
use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Testing\AbstractProviderTest;

final class ParsingProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            Core\LinkerInterface::class,
            Core\RendererInterface::class,

            CoreRepositories\PageRepositoryInterface::class,
            CoreRepositories\TagRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new GeneralProvider();
    }

    public function testWiring(): void
    {
        // $this->check(DoubleBracketsConfig::class);
        // $this->check(NewsLinkMapper::class);
        // $this->check(PageLinkMapper::class);
        // $this->check(TagLinkMapper::class);
    }
}
