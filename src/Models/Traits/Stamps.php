<?php

namespace App\Models\Traits;

use App\Models\User;
use Plasticode\Models\Traits\Stamps as BaseStamps;

/**
 * @method User|null creator()
 * @method User|null updater()
 * @method static withCreator(User|callable|null $creator)
 * @method static withUpdater(User|callable|null $updater)
 */
trait Stamps
{
    use BaseStamps;
}
