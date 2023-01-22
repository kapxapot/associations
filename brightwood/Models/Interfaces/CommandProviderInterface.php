<?php

namespace Brightwood\Models\Interfaces;

use Brightwood\Models\Command;

interface CommandProviderInterface
{
    public function toCommand(): Command;
}
