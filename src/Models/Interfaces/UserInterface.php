<?php

namespace App\Models\Interfaces;

use App\Models\TelegramUser;
use App\Models\User;

interface UserInterface
{
    public function toUser(): ?User;

    public function toTelegramUser(): ?TelegramUser;
}
