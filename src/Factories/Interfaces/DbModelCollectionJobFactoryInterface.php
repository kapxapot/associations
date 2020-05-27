<?php

namespace App\Factories\Interfaces;

use App\Jobs\Interfaces\DbModelCollectionJobInterface;

interface DbModelCollectionJobFactoryInterface
{
    function make() : DbModelCollectionJobInterface;
}
