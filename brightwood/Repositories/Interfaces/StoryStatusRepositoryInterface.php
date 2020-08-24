<?php

namespace Brightwood\Repositories\Interfaces;

use App\Models\TelegramUser;
use Brightwood\Models\StoryStatus;

interface StoryStatusRepositoryInterface
{
    function get(?int $id) : ?StoryStatus;
    function getByTelegramUser(TelegramUser $telegramUser) : ?StoryStatus;
    function save(StoryStatus $storyStatus) : StoryStatus;
    function store(array $data) : StoryStatus;
}
