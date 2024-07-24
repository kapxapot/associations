<?php

namespace Brightwood\Testing\Mocks\Repositories;

use App\Models\TelegramUser;
use Brightwood\Collections\StoryStatusCollection;
use Brightwood\Models\StoryStatus;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;

class StoryStatusRepositoryMock extends RepositoryMock implements StoryStatusRepositoryInterface
{
    /** @var HydratorInterface|ObjectProxy */
    private $hydrator;

    private StoryStatusCollection $statuses;

    /**
     * @param HydratorInterface|ObjectProxy $hydrator
     */
    public function __construct($hydrator)
    {
        $this->hydrator = $hydrator;
        $this->statuses = StoryStatusCollection::empty();
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

    public function store(array $data): StoryStatus
    {
        $status = StoryStatus::create($data);
        return $this->save($status);
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

        return $this->hydrator->hydrate($storyStatus);
    }
}
