<?php

namespace Brightwood\Repositories;

use App\Models\TelegramUser;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Util\Date;

class StoryStatusRepository extends IdiormRepository implements StoryStatusRepositoryInterface
{
    protected string $entityClass = StoryStatus::class;

    public function get(?int $id) : ?StoryStatus
    {
        return $this->getEntity($id);
    }

    public function getByTelegramUser(TelegramUser $telegramUser) : ?StoryStatus
    {
        return $this
            ->query()
            ->where('telegram_user_id', $telegramUser->getId())
            ->one();
    }

    public function save(StoryStatus $storyStatus) : StoryStatus
    {
        if ($storyStatus->isPersisted()) {
            $storyStatus->updatedAt = Date::dbNow();
        }

        return $this->saveEntity($storyStatus);
    }

    public function store(array $data) : StoryStatus
    {
        return $this->storeEntity($data);
    }
}
