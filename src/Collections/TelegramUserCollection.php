<?php

namespace App\Collections;

use App\Models\TelegramUser;
use Plasticode\Collections\Basic\DbModelCollection;

class TelegramUserCollection extends DbModelCollection
{
    protected string $class = TelegramUser::class;
}
