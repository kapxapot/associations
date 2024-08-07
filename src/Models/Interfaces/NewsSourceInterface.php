<?php

namespace App\Models\Interfaces;

use App\Models\User;
use Plasticode\Models\Interfaces\NewsSourceInterface as BaseNewsSourceInterface;

interface NewsSourceInterface extends BaseNewsSourceInterface
{
    public function creator(): ?User;
}
