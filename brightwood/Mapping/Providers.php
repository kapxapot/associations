<?php

namespace Brightwood\Mapping;

use Brightwood\Mapping\Providers\GeneralProvider;
use Plasticode\Collections\MappingProviderCollection;

class Providers extends MappingProviderCollection
{
    public function __construct()
    {
        parent::__construct(
            [
                new GeneralProvider()
            ]
        );
    }
}
