<?php

namespace Brightwood\Repositories\Interfaces;

use App\Models\TelegramUser;
use Brightwood\Models\StoryStatus;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface StoryStatusRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?StoryStatus;

    public function getByTelegramUser(TelegramUser $telegramUser): ?StoryStatus;

    public function store(array $data): StoryStatus;

    public function save(StoryStatus $storyStatus): StoryStatus;
}
