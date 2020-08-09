<?php

namespace App\Repositories\Brightwood;

use App\Models\Brightwood\StoryStatus;
use App\Models\TelegramUser;
use App\Repositories\Brightwood\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

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
        return $this->saveEntity($storyStatus);
    }

    public function store(array $data) : StoryStatus
    {
        return $this->storeEntity($data);
    }
}
