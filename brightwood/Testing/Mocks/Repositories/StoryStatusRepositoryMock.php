<?php

namespace Brightwood\Testing\Mocks\Repositories;

use Brightwood\Collections\StoryStatusCollection;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Models\TelegramUser;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class StoryStatusRepositoryMock implements StoryStatusRepositoryInterface
{
    private StoryStatusCollection $statuses;

    public function __construct(?ArraySeederInterface $seeder = null)
    {
        $this->statuses = $seeder
            ? StoryStatusCollection::make($seeder->seed())
            : StoryStatusCollection::empty();
    }

    public function get(?int $id): ?StoryStatus
    {
        return $this->statuses->first(
            fn (StoryStatus $s) => $s->getId() == $id
        );
    }

    public function getByTelegramUser(TelegramUser $telegramUser): ?StoryStatus
    {
        return $this->statuses->first(
            fn (StoryStatus $s) => $s->telegramUserId == $telegramUser->getId()
        );
    }

    public function save(StoryStatus $storyStatus): StoryStatus
    {
        if ($this->statuses->contains($storyStatus)) {
            return $storyStatus;
        }

        if (!$storyStatus->isPersisted()) {
            $storyStatus->id = $this->statuses->nextId();
        }

        $this->statuses = $this->statuses->add($storyStatus);

        return $storyStatus;
    }

    public function store(array $data): StoryStatus
    {
        $status = StoryStatus::create($data);

        return $this->save($status);
    }
}
