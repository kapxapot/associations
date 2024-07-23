<?php

namespace Brightwood\Models;

use App\Models\Traits\Created;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;

/**
 * @property integer $id
 * @property string $jsonData
 * @property integer $storyId
 * @method Story story()
 * @method static withStory(Story|callable $story)
 */
class StoryVersion extends DbModel implements CreatedInterface
{
    use Created;

    protected function requiredWiths(): array
    {
        return ['story'];
    }
}
