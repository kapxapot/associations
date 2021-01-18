<?php

namespace App\Collections;

use App\Models\TelegramUser;
use Plasticode\Collections\Generic\DbModelCollection;

class TelegramUserCollection extends DbModelCollection
{
    protected string $class = TelegramUser::class;
}
