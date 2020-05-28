<?php

namespace App\Jobs\Interfaces;

use Plasticode\Collections\Basic\DbModelCollection;

interface ModelJobInterface
{
    function run() : DbModelCollection;
}
