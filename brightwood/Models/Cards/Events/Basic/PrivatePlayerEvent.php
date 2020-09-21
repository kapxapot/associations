<?php

namespace Brightwood\Models\Cards\Events\Basic;

use Brightwood\Models\Cards\Players\Player;

abstract class PrivatePlayerEvent extends PublicPlayerEvent
{
    abstract public function privateChunk() : string;

    public function messageFor(?Player $player) : string
    {
        return $player && $player->isInspector()
            ? $this->privateMessage()
            : parent::messageFor($player);
    }

    private function privateMessage() : string
    {
        return $this->player . ' ' . $this->privateChunk();
    }
}
