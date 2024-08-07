<?php

namespace Brightwood\Testing\Mocks\Repositories;

use App\Models\TelegramUser;
use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Services\TelegramUserService;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class StoryRepositoryMock extends RepositoryMock implements StoryRepositoryInterface
{
    private TelegramUserService $telegramUserService;

    private StoryCollection $stories;

    public function __construct(
        TelegramUserService $telegramUserService,
        ?ArraySeederInterface $seeder = null
    )
    {
        $this->telegramUserService = $telegramUserService;

        $this->stories = $seeder
            ? StoryCollection::make($seeder->seed())
            : StoryCollection::empty();
    }

    public function get(?int $id): ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->getId() == $id
        );
    }

    public function getByUuid(string $uuid): ?Story
    {
        return $this->stories->first(
            fn (Story $s) => $s->uuid == $uuid
        );
    }

    public function getAll(): StoryCollection
    {
        return $this->stories;
    }

    public function getAllEditableBy(TelegramUser $tgUser): StoryCollection
    {
        $stories = $this->stories->where(
            fn (Story $s) => $s->hasUuid()
        );

        $user = $tgUser->user();

        if ($this->telegramUserService->isAdmin($tgUser)) {
            return $stories->where(
                fn (Story $s) => $s->creator() === null
                    || $s->creator()->equals($user)
            );
        }

        return $stories->where(
            fn (Story $s) => $s->creator()->equals($user)
        );
    }

    public function store(array $data): Story
    {
        $story = Story::create($data);
        return $this->save($story);
    }

    private function save(Story $story): Story
    {
        if ($this->stories->contains($story)) {
            return $story;
        }

        if (!$story->isPersisted()) {
            $story->id = $this->stories->nextId();
        }

        $this->stories = $this->stories->add($story);

        // return $this->hydrator->hydrate($version);
        return $story;
    }
}
