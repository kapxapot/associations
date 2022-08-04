<?php

namespace App\Models\DTO;

class GameOptions
{
    private bool $isGameStart = false;

    /**
     * @return $this
     */
    public function asGameStart(): self
    {
        $this->isGameStart = true;

        return $this;
    }

    public function isGameStart(): bool
    {
        return $this->isGameStart;
    }
}
