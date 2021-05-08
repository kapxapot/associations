<?php

namespace Brightwood\Repositories;

use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Models\TelegramUser;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Util\Date;

class StoryStatusRepository extends IdiormRepository implements StoryStatusRepositoryInterface
{
    protected function entityClass(): string
    {
        return StoryStatus::class;
    }

    public function get(?int $id): ?StoryStatus
    {
        return $this->getEntity($id);
    }

    public function getByTelegramUser(TelegramUser $telegramUser): ?StoryStatus
    {
        return $this
            ->query()
            ->where('telegram_user_id', $telegramUser->getId())
            ->one();
    }

    public function save(StoryStatus $storyStatus): StoryStatus
    {
        if ($storyStatus->isPersisted()) {
            $storyStatus->updatedAt = Date::dbNow();
        }

        return $this->saveEntity($storyStatus);
    }

    public function store(array $data): StoryStatus
    {
        return $this->storeEntity($data);
    }
}
