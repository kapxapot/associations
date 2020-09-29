<?php

namespace Brightwood\Models\Interfaces;

use Brightwood\Models\Command;

interface CommandProviderInterface
{
    function toCommand() : Command;
}
