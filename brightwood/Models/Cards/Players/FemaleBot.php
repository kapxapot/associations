<?php

namespace Brightwood\Models\Cards\Players;

use Plasticode\Util\Cases;

class FemaleBot extends Bot
{
    public function __construct(
        ?string $name = null
    )
    {
        parent::__construct($name, Cases::FEM);
    }
}
