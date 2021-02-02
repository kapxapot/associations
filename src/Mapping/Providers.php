<?php

namespace App\Mapping;

use App\Mapping\Providers\CoreProvider;
use App\Mapping\Providers\EventProvider;
use App\Mapping\Providers\ParsingProvider;
use App\Mapping\Providers\RepositoryProvider;
use App\Mapping\Providers\ServiceProvider;
use App\Mapping\Providers\SlimProvider;
use App\Mapping\Providers\SpecificationProvider;
use Plasticode\Collections\MappingProviderCollection;

class Providers extends MappingProviderCollection
{
    public function __construct()
    {
        parent::__construct(
            [
                new SlimProvider(),
                new CoreProvider(),
                new ParsingProvider(),
                new RepositoryProvider(),
                new ServiceProvider(),
                new EventProvider(),
                new SpecificationProvider()
            ]
        );
    }
}
