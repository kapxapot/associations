<?php

namespace App\Models;

use App\Models\Brightwood\StoryStatus;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;
use Plasticode\Util\Cases;

/**
 * @property integer|null $genderId
 * @property integer $id
 * @property int|null $userId
 * @property integer $telegramId
 * @property string $username
 * @property string|null $firstName
 * @property string|null $lastName
 * @method StoryStatus|null storyStatus()
 * @method User|null user()
 * @method static withStoryStatus(StoryStatus|callable|null $storyStatus)
 * @method static withUser(User|callable|null $user)
 */
class TelegramUser extends DbModel
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return [
            'storyStatus',
            'user'
        ];
    }

    public function isValid() : bool
    {
        return $this->user() !== null;
    }

    public function isNew() : bool
    {
        return $this->isValid() && is_null($this->user()->lastGame());
    }

    public function privateName() : string
    {
        return $this->firstName ?? $this->lastName ?? $this->username ?? 'инкогнито';
    }

    public function publicName() : string
    {
        return $this->username ?? $this->fullName() ?? 'инкогнито';
    }

    public function fullName() : ?string
    {
        $parts = [$this->firstName, $this->lastName];

        $fullName = implode(' ', array_filter($parts));

        return (strlen($fullName) > 0)
            ? $fullName
            : $this->username;
    }

    /**
     * Returns true, if the Telegram user started to read a story.
     */
    public function isReader() : bool
    {
        return $this->storyStatus() !== null;
    }

    public function gender() : int
    {
        return $this->genderId ?? Cases::MAS;
    }
}
