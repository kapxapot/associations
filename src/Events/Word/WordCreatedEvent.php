<?php

namespace App\Events\Word;

use App\Events\Traits\SyncTrait;

class WordCreatedEvent extends WordEvent
{
    use SyncTrait;
}
