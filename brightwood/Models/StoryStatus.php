<?php

namespace Brightwood\Models;

use App\Models\TelegramUser;
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
 * @property integer $stepId
 * @method TelegramUser telegramUser()
 * @method static withTelegramUser(TelegramUser|callable $telegramUser)
 */
class StoryStatus extends DbModel implements CreatedAtInterface, UpdatedAtInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['telegramUser'];
    }

    public function data(): ?array
    {
        return $this->jsonData
            ? json_decode($this->jsonData, true)
            : null;
    }
}
