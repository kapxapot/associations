<?php

namespace App\Jobs\Interfaces;

use Plasticode\Collections\Basic\DbModelCollection;

interface DbModelCollectionJobInterface
{
    function run() : DbModelCollection;
}
