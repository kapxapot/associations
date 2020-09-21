<?php

namespace Brightwood\Models\Cards\Events\Basic;

use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;

abstract class PublicPlayerEvent implements CardEventInterface
{
    protected Player $player;

    public function __construct(
        Player $player
    )
    {
        $this->player = $player;
    }

    public function player() : Player
    {
        return $this->player;
    }

    abstract public function publicChunk() : string;

    abstract public function personalChunk() : string;

    public function messageFor(?Player $player) : string
    {
        return $this->player->equals($player)
            ? $this->personalMessage()
            : $this->publicMessage();
    }

    private function publicMessage() : string
    {
        return $this->player . ' ' . $this->publicChunk();
    }

    private function personalMessage() : string
    {
        return 'Вы ' . $this->personalChunk();
    }
}
