<?php

namespace Brightwood\Models;

use App\Models\Traits\Created;
use App\Models\User;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property string $jsonData
 * @property string $uuid
 * @method User creator()
 * @method static withCreator(User|callable $creator)
 */
class StoryCandidate extends DbModel implements CreatedInterface, UpdatedAtInterface
{
    use Created;
    use UpdatedAt;
}
