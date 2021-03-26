<?php

namespace App\Events\Traits;

/**
 * Implementation of {@see App\Events\Interfaces\SyncEventInterface}.
 */
trait SyncTrait
{
    private bool $isSync = false;

    /**
     * Should the event be processed synchronously?
     */
    public function isSync(): bool
    {
        return $this->isSync;
    }

    /**
     * @return $this
     */
    public function asSync(): self
    {
        $this->isSync = true;

        return $this;
    }
}
