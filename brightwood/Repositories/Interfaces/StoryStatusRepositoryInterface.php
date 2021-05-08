<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Models\StoryStatus;
use Plasticode\Models\TelegramUser;

interface StoryStatusRepositoryInterface
{
    public function get(?int $id): ?StoryStatus;

    public function getByTelegramUser(TelegramUser $telegramUser): ?StoryStatus;

    public function save(StoryStatus $storyStatus): StoryStatus;

    public function store(array $data): StoryStatus;
}
