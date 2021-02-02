<?php

namespace App\Mapping;

use App\Mapping\Providers\EventProvider;
use App\Mapping\Providers\GeneralProvider;
use App\Mapping\Providers\ParsingProvider;
use App\Mapping\Providers\RepositoryProvider;
use App\Mapping\Providers\ServiceProvider;
use Plasticode\Collections\MappingProviderCollection;

class Providers extends MappingProviderCollection
{
    public function __construct()
    {
        parent::__construct(
            [
                new GeneralProvider(),
                new ParsingProvider(),
                new RepositoryProvider(),
                new ServiceProvider(),
                new EventProvider()
            ]
        );
    }
}
