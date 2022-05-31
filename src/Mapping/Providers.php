<?php

namespace App\Mapping;

use App\Mapping\Providers\EventProvider;
use App\Mapping\Providers\GeneralProvider;
use App\Mapping\Providers\QueryLogProvider;
use App\Mapping\Providers\RepositoryProvider;
use Plasticode\Collections\MappingProviderCollection;

class Providers extends MappingProviderCollection
{
    public function __construct()
    {
        parent::__construct([
            new GeneralProvider(),
            new RepositoryProvider(),
            new QueryLogProvider(),
            new EventProvider()
        ]);
    }
}
