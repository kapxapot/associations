<?php

namespace App\Jobs\Interfaces;

use Plasticode\Collections\Generic\DbModelCollection;

interface ModelJobInterface
{
    function run(): DbModelCollection;
}
