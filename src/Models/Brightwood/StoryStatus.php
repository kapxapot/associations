<?php

namespace App\Models\Brightwood;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property integer $telegramUserId
 * @property integer $storyId
 * @property integer $stepId
 * @method TelegramUser telegramUser()
 * @method static withTelegramUser(TelegramUser|callable $telegramUser)
 */
class StoryStatus extends DbModel
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['telegramUser'];
    }
}
