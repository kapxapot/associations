<?php

namespace Brightwood\Models;

use App\Models\TelegramUser;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property string|null $jsonData
 * @property integer $telegramUserId
 * @property integer $storyId
 * @property integer|null $storyVersionId
 * @property integer $stepId
 * @method Story story()
 * @method StoryVersion|null storyVersion()
 * @method TelegramUser telegramUser()
 * @method static withStory(Story|callable $story)
 * @method static withStoryVersion(StoryVersion|callable|null $storyVersion)
 * @method static withTelegramUser(TelegramUser|callable $telegramUser)
 */
class StoryStatus extends DbModel implements CreatedAtInterface, UpdatedAtInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['story', 'storyVersion', 'telegramUser'];
    }

    public function data(): ?array
    {
        return $this->jsonData
            ? json_decode($this->jsonData, true)
            : null;
    }
}
