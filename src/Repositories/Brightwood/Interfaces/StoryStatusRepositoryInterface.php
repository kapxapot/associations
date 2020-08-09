<?php

namespace App\Repositories\Brightwood\Interfaces;

use App\Models\Brightwood\StoryStatus;
use App\Models\TelegramUser;

interface StoryStatusRepositoryInterface
{
    function get(?int $id) : ?StoryStatus;
    function getByTelegramUser(TelegramUser $telegramUser) : ?StoryStatus;
    function save(StoryStatus $storyStatus) : StoryStatus;
    function store(array $data) : StoryStatus;
}
