<?php

namespace App\Models\Interfaces;

use App\Models\User;
use Plasticode\Models\Interfaces\NewsSourceInterface as BaseNewsSourceInterface;

interface NewsSourceInterface extends BaseNewsSourceInterface
{
    function creator() : ?User;
}
