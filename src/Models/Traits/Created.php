<?php

namespace App\Models\Traits;

use App\Models\User;
use Plasticode\Models\Traits\Created as BaseCreated;

/**
 * @method User|null creator()
 * @method static withCreator(User|callable|null $creator)
 */
trait Created
{
    use BaseCreated;
}
