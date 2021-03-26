<?php

namespace App\Events\Interfaces;

/**
 * The event that can be marked as synchronous or asynchronous.
 */
interface SyncEventInterface
{
    public function isSync(): bool;

    /**
     * @return $this
     */
    public function asSync(): self;
}
