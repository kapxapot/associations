<?php

namespace Brightwood\Models\Cards\Players;

use Plasticode\Semantics\Gender;

class FemaleBot extends Bot
{
    public function __construct(
        ?string $name = null
    )
    {
        parent::__construct($name, Gender::FEM);
    }
}
