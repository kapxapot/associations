<?php

namespace App\Events\Word;

use App\Events\Traits\SyncTrait;

class WordUpdatedEvent extends WordEvent
{
    use SyncTrait;
}
