<?php

namespace App\Factories\Interfaces;

use App\Jobs\Interfaces\ModelJobInterface;

interface ModelJobFactoryInterface
{
    function make() : ModelJobInterface;
}
