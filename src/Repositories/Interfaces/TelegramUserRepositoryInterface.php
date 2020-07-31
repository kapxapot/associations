<?php

namespace App\Repositories\Interfaces;

use App\Models\TelegramUser;
use App\Models\User;

interface TelegramUserRepositoryInterface
{
    function get(?int $id) : ?TelegramUser;
    function getByTelegramId(int $id) : ?TelegramUser;
    function getByUser(User $user) : ?TelegramUser;
    function save(TelegramUser $user) : TelegramUser;
    function store(array $data) : TelegramUser;
}
