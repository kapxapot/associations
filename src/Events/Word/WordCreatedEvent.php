<?php

namespace App\Events\Word;

use App\Events\Interfaces\SyncEventInterface;
use App\Events\Traits\SyncTrait;

class WordCreatedEvent extends WordEvent implements SyncEventInterface
{
    use SyncTrait;
}
