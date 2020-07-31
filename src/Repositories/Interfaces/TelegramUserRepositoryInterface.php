<?php

namespace App\Repositories\Interfaces;

use App\Models\TelegramUser;

interface TelegramUserRepositoryInterface
{
    function get(?int $id) : ?TelegramUser;
    function getByTelegramId(int $id) : ?TelegramUser;
    function save(TelegramUser $user) : TelegramUser;
    function store(array $data) : TelegramUser;
}
