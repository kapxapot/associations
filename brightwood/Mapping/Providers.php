<?php

namespace Brightwood\Mapping;

use Brightwood\Mapping\Providers\CardsProvider;
use Brightwood\Mapping\Providers\CoreProvider;
use Brightwood\Mapping\Providers\ExternalProvider;
use Brightwood\Mapping\Providers\RepositoryProvider;
use Plasticode\Collections\MappingProviderCollection;

class Providers extends MappingProviderCollection
{
    public function __construct()
    {
        parent::__construct(
            [
                new CoreProvider(),
                new RepositoryProvider(),
                new ExternalProvider(),
                new CardsProvider()
            ]
        );
    }
}
